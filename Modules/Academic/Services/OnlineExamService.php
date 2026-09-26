<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Academic\Entities\OnlineExam;
use Modules\Academic\Entities\StudentSession;

final class OnlineExamService
{
    public function getExamsForSession(StudentSession $session): Collection
    {
        if (Schema::hasTable('onlineexam')) {
            // CI: getstudentexamlist() filters exam_to >= now (upcoming tab).
            // CI: getStudentexam() selects counter = attempts count per student.
            return DB::table('onlineexam')
                ->join('onlineexam_students', 'onlineexam_students.onlineexam_id', '=', 'onlineexam.id')
                ->where('onlineexam_students.student_session_id', $session->id)
                ->where('onlineexam.is_active', 1)
                ->where('onlineexam.exam_to', '>=', now())
                ->select([
                    'onlineexam.*',
                    'onlineexam_students.id as onlineexam_student_id',
                    'onlineexam_students.is_attempted',
                    'onlineexam_students.rank',
                    DB::raw('(select count(*) from onlineexam_attempts where onlineexam_attempts.onlineexam_student_id = onlineexam_students.id) as counter'),
                    DB::raw('(select count(*) from onlineexam_questions where onlineexam_questions.onlineexam_id = onlineexam.id) as total_ques'),
                ])
                ->orderByDesc('onlineexam.exam_from')
                ->get();
        }

        return OnlineExam::query()
            ->active()
            ->forClassSection((int) $session->class_id, (int) $session->section_id)
            ->get();
    }

    public function getClosedExamsForSession(StudentSession $session): Collection
    {
        if (Schema::hasTable('onlineexam')) {
            // CI: getstudentclosedexamlist() filters exam_to < now (closed tab).
            return DB::table('onlineexam')
                ->join('onlineexam_students', 'onlineexam_students.onlineexam_id', '=', 'onlineexam.id')
                ->where('onlineexam_students.student_session_id', $session->id)
                ->where('onlineexam.is_active', 1)
                ->where('onlineexam.exam_to', '<', now())
                ->select([
                    'onlineexam.*',
                    'onlineexam_students.id as onlineexam_student_id',
                    'onlineexam_students.is_attempted',
                    'onlineexam_students.rank',
                    DB::raw('(select count(*) from onlineexam_attempts where onlineexam_attempts.onlineexam_student_id = onlineexam_students.id) as counter'),
                    DB::raw('(select count(*) from onlineexam_questions where onlineexam_questions.onlineexam_id = onlineexam.id) as total_ques'),
                ])
                ->orderByDesc('onlineexam.exam_from')
                ->get();
        }

        return OnlineExam::query()
            ->active()
            ->forClassSection((int) $session->class_id, (int) $session->section_id)
            ->where('exam_to', '<', now())
            ->get();
    }

    /**
     * @return array{exam: object, student: object|null, onlineExamStudent: object|null, questions: Collection, stats: array, publishResult: bool}
     */
    public function getExamDetail(int $examId, StudentSession $session): array
    {
        $student = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.id', $session->student_id)
            ->select([
                'students.id',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.admission_no',
                'students.father_name',
                'classes.class',
                'sections.section',
            ])
            ->first();

        $exam = Schema::hasTable('onlineexam')
            ? DB::table('onlineexam')->where('id', $examId)->first()
            : null;

        if (! $exam && Schema::hasTable('online_exams')) {
            $exam = OnlineExam::find($examId);
        }

        if (! $exam) {
            abort(404, 'Exam not found');
        }

        $onlineExamStudent = Schema::hasTable('onlineexam_students')
            ? DB::table('onlineexam_students')
                ->where('onlineexam_id', $examId)
                ->where('student_session_id', $session->id)
                ->first()
            : null;

        $onlineExamStudentId = (int) ($onlineExamStudent->id ?? 0);

        $questions = $this->getQuestionsWithResults($examId, $onlineExamStudentId);
        $stats = $this->computeStats($questions, (int) ($exam->is_neg_marking ?? 0));
        $publishResult = $this->resolvePublishResult($exam, (int) ($onlineExamStudent->is_attempted ?? 0));

        return [
            'exam' => $exam,
            'student' => $student,
            'onlineExamStudent' => $onlineExamStudent,
            'questions' => $questions,
            'stats' => $stats,
            'publishResult' => $publishResult,
        ];
    }

    /**
     * Mirrors CI user/Onlineexam::getExamForm().
     * CI logic: if now >= exam_to => question_status=1 (blocked);
     * else if attempt > attempts_count => record new attempt, status=0 (allowed);
     * else question_status=1 (max attempts reached).
     * Also adjusts duration to min(remaining_time, exam.duration).
     *
     * @return array{exam: object, questions: Collection, duration: string, question_status: int, total_question: int, onlineexam_student_id: int}
     */
    public function startExam(int $examId, StudentSession $session): array
    {
        return DB::transaction(function () use ($examId, $session): array {
            $exam = DB::table('onlineexam')->where('id', $examId)->lockForUpdate()->first();

            if (! $exam) {
                abort(404, 'Exam not found');
            }

            // CI: question_status=1 when exam date passed.
            if (! empty($exam->exam_to) && Carbon::now()->gte(Carbon::parse($exam->exam_to))) {
                abort(422, 'You have reached total attempts or exam date passed, please contact to administrator');
            }

            $onlineExamStudent = DB::table('onlineexam_students')
                ->where('onlineexam_id', $examId)
                ->where('student_session_id', $session->id)
                ->lockForUpdate()
                ->first();

            $onlineExamStudentId = $onlineExamStudent->id ?? null;

            if (! $onlineExamStudentId) {
                $onlineExamStudentId = DB::table('onlineexam_students')->insertGetId([
                    'onlineexam_id' => $examId,
                    'student_session_id' => $session->id,
                    'is_attempted' => 0,
                    'rank' => 0,
                    'quiz_attempted' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $attempts = DB::table('onlineexam_attempts')
                ->where('onlineexam_student_id', $onlineExamStudentId)
                ->count();

            $maxAttempts = (int) ($exam->attempt ?? 1);

            // CI: $exam->attempt > $getStudentAttemts => allow + add attempt.
            if ($attempts >= $maxAttempts) {
                abort(422, 'You have reached total attempts or exam date passed, please contact to administrator');
            }

            DB::table('onlineexam_attempts')->insert([
                'onlineexam_student_id' => $onlineExamStudentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // CI: getExamQuestions($recordid, $exam->is_random_question).
            $isRandom = (bool) ($exam->is_random_question ?? $exam->is_random ?? false);
            $questionsQuery = DB::table('onlineexam_questions')
                ->join('questions', 'questions.id', '=', 'onlineexam_questions.question_id')
                ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
                ->where('onlineexam_questions.onlineexam_id', $examId)
                ->select([
                    'onlineexam_questions.id as onlineexam_question_id',
                    'onlineexam_questions.marks',
                    'onlineexam_questions.neg_marks',
                    'onlineexam_questions.question_id',
                    'questions.question',
                    'questions.question_type',
                    'questions.level',
                    'questions.opt_a',
                    'questions.opt_b',
                    'questions.opt_c',
                    'questions.opt_d',
                    'questions.opt_e',
                    // Never expose answer key on start; detail exposes it only when published.
                    'questions.descriptive_word_limit',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code',
                ]);
            if ($isRandom) {
                $questionsQuery->inRandomOrder();
            } else {
                $questionsQuery->orderByDesc('onlineexam_questions.id');
            }
            $questions = $questionsQuery->get();

            // CI duration adjustment: min(remaining, exam.duration).
            $duration = (string) ($exam->duration ?? '00:30:00');
            if (! empty($exam->exam_to)) {
                $remaining = (int) round((Carbon::parse($exam->exam_to)->timestamp - Carbon::now()->timestamp));
                if ($remaining < 0) {
                    $remaining = 0;
                }
                $examSecs = $this->hmsToSeconds($duration);
                $duration = $remaining < $examSecs ? $this->secondsToHms($remaining) : $duration;
            }

            return [
                'exam' => $exam,
                'questions' => $questions,
                'duration' => $duration,
                'question_status' => 0,
                'total_question' => $questions->count(),
                'onlineexam_student_id' => (int) $onlineExamStudentId,
            ];
        });
    }

    private function hmsToSeconds(string $time): int
    {
        // Mirrors CI getSecondsFromHMS().
        $parts = array_reverse(explode(':', $time));
        $seconds = 0;
        foreach ($parts as $key => $value) {
            if ($key > 2) {
                break;
            }
            $seconds += (60 ** $key) * (int) $value;
        }
        return $seconds;
    }

    private function secondsToHms(int $seconds): string
    {
        // Mirrors CI getHMSFromSeconds().
        $seconds = max(0, (int) round($seconds));
        return sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);
    }

    /**
     * @param  array<int, array{question_id: mixed, answer?: mixed, select_option?: mixed}>  $answers
     */
    public function submitExam(int $examId, array $answers, StudentSession $session): int
    {
        return DB::transaction(function () use ($examId, $answers, $session): int {
            $onlineExamStudent = DB::table('onlineexam_students')
                ->where('onlineexam_id', $examId)
                ->where('student_session_id', $session->id)
                ->lockForUpdate()
                ->first();

            $onlineExamStudentId = $onlineExamStudent->id ?? null;

            if (! $onlineExamStudentId) {
                $onlineExamStudentId = DB::table('onlineexam_students')->insertGetId([
                    'onlineexam_id' => $examId,
                    'student_session_id' => $session->id,
                    'is_attempted' => 1,
                    'rank' => 0,
                    'quiz_attempted' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('onlineexam_students')
                    ->where('id', $onlineExamStudentId)
                    ->update([
                        'is_attempted' => 1,
                        'quiz_attempted' => 1,
                        'updated_at' => now(),
                    ]);
            }

            if ($answers === []) {
                return (int) $onlineExamStudentId;
            }

            // Batch-load exam questions + bank rows — 2 queries, no N+1.
            $questionIds = collect($answers)->pluck('question_id')->filter()->unique()->values();

            $examQuestions = DB::table('onlineexam_questions')
                ->where('onlineexam_id', $examId)
                ->whereIn('question_id', $questionIds)
                ->get()
                ->keyBy('question_id');

            $bankQuestions = DB::table('questions')
                ->whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');

            $now = now();
            $rows = [];

            foreach ($answers as $ans) {
                $qId = $ans['question_id'] ?? null;
                if ($qId === null) {
                    continue;
                }

                $selected = $ans['answer'] ?? ($ans['select_option'] ?? null);
                $eq = $examQuestions->get($qId);
                $qRecord = $bankQuestions->get($qId);
                $eqId = $eq->id ?? $qId;

                $rows[] = [
                    'onlineexam_student_id' => $onlineExamStudentId,
                    'onlineexam_question_id' => $eqId,
                    'select_option' => is_array($selected) ? json_encode($selected) : (string) $selected,
                    'marks' => $this->scoreAnswer($qRecord, $eq, $selected),
                    'remark' => '',
                    'attachment_name' => '',
                    'attachment_upload_name' => '',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('onlineexam_student_results')->upsert(
                $rows,
                ['onlineexam_student_id', 'onlineexam_question_id'],
                ['select_option', 'marks', 'updated_at']
            );

            return (int) $onlineExamStudentId;
        });
    }

    private function getQuestionsWithResults(int $examId, int $onlineExamStudentId): Collection
    {
        if (Schema::hasTable('onlineexam_questions') && Schema::hasTable('questions')) {
            return DB::table('onlineexam_questions')
                ->join('questions', 'questions.id', '=', 'onlineexam_questions.question_id')
                ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
                ->leftJoin('onlineexam_student_results', function ($join) use ($onlineExamStudentId): void {
                    $join->on('onlineexam_student_results.onlineexam_question_id', '=', 'onlineexam_questions.id')
                        ->where('onlineexam_student_results.onlineexam_student_id', '=', $onlineExamStudentId);
                })
                ->where('onlineexam_questions.onlineexam_id', $examId)
                ->select([
                    'onlineexam_questions.id as onlineexam_question_id',
                    'onlineexam_questions.marks',
                    'onlineexam_questions.neg_marks',
                    'onlineexam_questions.question_id',
                    'questions.question',
                    'questions.question_type',
                    'questions.level',
                    'questions.opt_a',
                    'questions.opt_b',
                    'questions.opt_c',
                    'questions.opt_d',
                    'questions.opt_e',
                    'questions.correct',
                    'questions.descriptive_word_limit',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code',
                    'onlineexam_student_results.select_option',
                    'onlineexam_student_results.marks as score_marks',
                    'onlineexam_student_results.remark',
                    'onlineexam_student_results.attachment_name',
                    'onlineexam_student_results.attachment_upload_name',
                ])
                ->get();
        }

        if (Schema::hasTable('online_exam_questions')) {
            return DB::table('online_exam_questions')->where('online_exam_id', $examId)->get();
        }

        return collect();
    }

    private function computeStats(Collection $questions, int $isNegativeMarking): array
    {
        $correct = 0;
        $wrong = 0;
        $notAttempted = 0;
        $totalMarks = 0.0;
        $scoredMarks = 0.0;
        $negativeMarks = 0.0;
        $descriptive = 0;

        foreach ($questions as $q) {
            $qMarks = (float) ($q->marks ?? 0);
            $qNegMarks = (float) ($q->neg_marks ?? 0);
            $qType = $q->question_type ?? 'singlechoice';
            $selected = $q->select_option ?? null;
            $correctAnswer = $q->correct ?? null;
            $scoreMarks = (float) ($q->score_marks ?? 0);

            $totalMarks += $qMarks;
            $scoredMarks += $scoreMarks;

            if ($qType === 'descriptive') {
                $descriptive++;
            }

            if ($selected !== null && $selected !== '') {
                if ($qType === 'singlechoice' || $qType === 'true_false') {
                    if ($selected == $correctAnswer) {
                        $correct++;
                    } else {
                        $wrong++;
                        $negativeMarks += $qNegMarks;
                    }
                } elseif ($qType === 'multichoice') {
                    $selectedArr = json_decode((string) $selected, true) ?? [];
                    $correctArr = json_decode((string) $correctAnswer, true) ?? [];
                    sort($selectedArr);
                    sort($correctArr);
                    if ($selectedArr === $correctArr) {
                        $correct++;
                    } else {
                        $wrong++;
                        $negativeMarks += $qNegMarks;
                    }
                } elseif ($scoreMarks > 0) {
                    $correct++;
                }
            } else {
                $notAttempted++;
            }
        }

        if (! $isNegativeMarking) {
            $negativeMarks = 0.0;
        }

        $finalScored = max(0.0, $scoredMarks - $negativeMarks);

        return [
            'total_questions' => $questions->count(),
            'descriptive_questions' => $descriptive,
            'correct_answers' => $correct,
            'wrong_answers' => $wrong,
            'not_attempted' => $notAttempted,
            'total_exam_marks' => $totalMarks,
            'total_negative_marks' => $negativeMarks,
            'total_scored_marks' => $finalScored,
            'score_percentage' => $totalMarks > 0 ? round(($finalScored * 100) / $totalMarks, 2) : 0,
        ];
    }

    private function resolvePublishResult(object $exam, int $isAttempted): bool
    {
        $isQuiz = (int) ($exam->is_quiz ?? 0);
        $publishResult = (bool) ($exam->publish_result ?? false);
        $autoPublishDate = $exam->auto_publish_date ?? null;

        if ($isAttempted === 1 && $isQuiz === 1) {
            return true;
        }

        if (! $publishResult && ! empty($autoPublishDate) && ! in_array($autoPublishDate, ['0000-00-00 00:00:00', '0000-00-00'], true)) {
            return Carbon::parse($autoPublishDate)->lte(Carbon::now());
        }

        return $publishResult;
    }

    private function scoreAnswer(mixed $qRecord, mixed $eq, mixed $selected): float
    {
        if (! $qRecord || ! $eq) {
            return 0.0;
        }

        $type = $qRecord->question_type ?? '';

        if ($type === 'singlechoice' || $type === 'true_false') {
            return $selected == $qRecord->correct ? (float) $eq->marks : 0.0;
        }

        if ($type === 'multichoice') {
            $selectedArr = is_array($selected) ? $selected : json_decode((string) $selected, true);
            $correctArr = json_decode((string) $qRecord->correct, true);

            if (is_array($selectedArr) && is_array($correctArr)) {
                sort($selectedArr);
                sort($correctArr);

                return $selectedArr === $correctArr ? (float) $eq->marks : 0.0;
            }
        }

        return 0.0;
    }
}
