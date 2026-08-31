<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\OnlineExam;
use Modules\Academic\Entities\OnlineExamQuestion;
use Modules\Academic\Entities\OnlineExamResult;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OnlineExamController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('OnlineExamController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::find($studentSession->student_id);

        if (Schema::hasTable('onlineexam')) {
            $examList = DB::table('onlineexam')
                ->join('onlineexam_students', 'onlineexam_students.onlineexam_id', '=', 'onlineexam.id')
                ->where('onlineexam_students.student_session_id', $studentSession->id)
                ->where('onlineexam.is_active', '1')
                ->select([
                    'onlineexam.*',
                    'onlineexam_students.id as onlineexam_student_id',
                    'onlineexam_students.is_attempted',
                    'onlineexam_students.rank',
                ])
                ->get();
        } else {
            $examList = OnlineExam::where('class_id', $studentSession->class_id)
                ->where('section_id', $studentSession->section_id)
                ->where('is_active', 1)
                ->get();
        }

        $data = [
            'student' => $student,
            'examList' => $examList,
        ];

        return $this->successResponse($data);
    }

    public function exam_detail($id): JsonResponse
    {
        $user = request()->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $student = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.id', $studentSession->student_id)
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

        $exam = null;
        if (Schema::hasTable('onlineexam')) {
            $exam = DB::table('onlineexam')->where('id', $id)->first();
        }

        if (!$exam && Schema::hasTable('online_exams')) {
            $exam = OnlineExam::find($id);
        }

        if (!$exam) {
            return $this->errorResponse('Exam not found', null, 404);
        }

        $onlineExamStudent = null;
        if (Schema::hasTable('onlineexam_students')) {
            $onlineExamStudent = DB::table('onlineexam_students')
                ->where('onlineexam_id', $id)
                ->where('student_session_id', $studentSession->id)
                ->first();
        }

        $onlineExamStudentId = $onlineExamStudent->id ?? 0;
        $isAttempted = $onlineExamStudent ? (int)$onlineExamStudent->is_attempted : 0;
        $rank = $onlineExamStudent ? (int)$onlineExamStudent->rank : 0;

        $isQuiz = isset($exam->is_quiz) ? (int)$exam->is_quiz : 0;
        $publishResult = isset($exam->publish_result) ? (bool)$exam->publish_result : false;
        $autoPublishDate = $exam->auto_publish_date ?? null;

        if ($isAttempted == 1 && $isQuiz) {
            $publishResult = true;
        } elseif (!empty($autoPublishDate) && $autoPublishDate != '0000-00-00 00:00:00' && $autoPublishDate != '0000-00-00') {
            if (!$publishResult) {
                $publishResult = (Carbon::parse($autoPublishDate)->timestamp <= now()->timestamp);
            }
        }

        $questionsList = [];
        if (Schema::hasTable('onlineexam_questions') && Schema::hasTable('questions')) {
            $questionsList = DB::table('onlineexam_questions')
                ->join('questions', 'questions.id', '=', 'onlineexam_questions.question_id')
                ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
                ->leftJoin('onlineexam_student_results', function ($join) use ($onlineExamStudentId) {
                    $join->on('onlineexam_student_results.onlineexam_question_id', '=', 'onlineexam_questions.id')
                         ->where('onlineexam_student_results.onlineexam_student_id', '=', $onlineExamStudentId);
                })
                ->where('onlineexam_questions.onlineexam_id', $id)
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
        } elseif (Schema::hasTable('online_exam_questions')) {
            $questionsList = OnlineExamQuestion::where('online_exam_id', $id)->get();
        }

        $correctCount = 0;
        $wrongCount = 0;
        $notAttemptedCount = 0;
        $totalExamMarks = 0;
        $totalScoredMarks = 0;
        $totalNegativeMarks = 0;
        $descriptiveCount = 0;
        $isNegativeMarking = isset($exam->is_neg_marking) ? (int)$exam->is_neg_marking : 0;

        foreach ($questionsList as $q) {
            $qMarks = isset($q->marks) ? (float)$q->marks : 0;
            $qNegMarks = isset($q->neg_marks) ? (float)$q->neg_marks : 0;
            $qType = $q->question_type ?? 'singlechoice';
            $selected = $q->select_option ?? null;
            $correct = $q->correct ?? null;
            $scoreMarks = isset($q->score_marks) ? (float)$q->score_marks : 0;

            $totalExamMarks += $qMarks;
            $totalScoredMarks += $scoreMarks;

            if ($qType == 'descriptive') {
                $descriptiveCount++;
            }

            if ($selected !== null && $selected !== '') {
                if ($qType == 'singlechoice' || $qType == 'true_false') {
                    if ($selected == $correct) {
                        $correctCount++;
                    } else {
                        $wrongCount++;
                        $totalNegativeMarks += $qNegMarks;
                    }
                } elseif ($qType == 'multichoice') {
                    $selectedArr = json_decode($selected, true) ?? [];
                    $correctArr = json_decode($correct, true) ?? [];
                    sort($selectedArr);
                    sort($correctArr);
                    if ($selectedArr == $correctArr) {
                        $correctCount++;
                    } else {
                        $wrongCount++;
                        $totalNegativeMarks += $qNegMarks;
                    }
                } else {
                    if ($scoreMarks > 0) {
                        $correctCount++;
                    }
                }
            } else {
                $notAttemptedCount++;
                $totalNegativeMarks += $qNegMarks;
            }
        }

        if (!$isNegativeMarking) {
            $totalNegativeMarks = 0;
        }

        $finalScored = max(0, $totalScoredMarks - $totalNegativeMarks);
        $scorePercentage = $totalExamMarks > 0 ? round(($finalScored * 100) / $totalExamMarks, 2) : 0;

        $examResultData = (array)$exam;
        $examResultData['is_attempted'] = $isAttempted;
        $examResultData['publish_result'] = $publishResult ? 1 : 0;
        $examResultData['rank'] = $rank;
        $examResultData['student'] = $student;
        $examResultData['total_questions'] = count($questionsList);
        $examResultData['descriptive_questions'] = $descriptiveCount;
        $examResultData['correct_answers'] = $correctCount;
        $examResultData['wrong_answers'] = $wrongCount;
        $examResultData['not_attempted'] = $notAttemptedCount;
        $examResultData['total_exam_marks'] = $totalExamMarks;
        $examResultData['total_negative_marks'] = $totalNegativeMarks;
        $examResultData['total_scored_marks'] = $finalScored;
        $examResultData['score_percentage'] = $scorePercentage;

        $data = [
            'result' => (object)$examResultData,
            'questions' => $questionsList,
        ];

        return $this->successResponse($data);
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'onlineexam_id' => 'required',
            'answers' => 'required',
        ]);

        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $examId = $request->onlineexam_id;
        $answers = is_string($request->answers) ? json_decode($request->answers, true) : $request->answers;

        if (Schema::hasTable('onlineexam_students')) {
            $onlineExamStudent = DB::table('onlineexam_students')
                ->where('onlineexam_id', $examId)
                ->where('student_session_id', $studentSession->id)
                ->first();

            $onlineExamStudentId = $onlineExamStudent->id ?? null;

            if (!$onlineExamStudentId) {
                $onlineExamStudentId = DB::table('onlineexam_students')->insertGetId([
                    'onlineexam_id' => $examId,
                    'student_session_id' => $studentSession->id,
                    'is_attempted' => 1,
                    'rank' => 0,
                    'quiz_attempted' => 1,
                    'created_at' => now(),
                    'updated_at' => now()->toDateString(),
                ]);
            } else {
                DB::table('onlineexam_students')
                    ->where('id', $onlineExamStudentId)
                    ->update([
                        'is_attempted' => 1,
                        'quiz_attempted' => 1,
                        'updated_at' => now()->toDateString(),
                    ]);
            }

            DB::table('onlineexam_attempts')->insert([
                'onlineexam_student_id' => $onlineExamStudentId,
                'created_at' => now(),
                'updated_at' => now()->toDateString(),
            ]);

            if (is_array($answers) && Schema::hasTable('onlineexam_student_results')) {
                foreach ($answers as $ans) {
                    $qId = $ans['question_id'] ?? null;
                    $selected = $ans['answer'] ?? ($ans['select_option'] ?? null);

                    $eq = DB::table('onlineexam_questions')
                        ->where('onlineexam_id', $examId)
                        ->where('question_id', $qId)
                        ->first();

                    $eqId = $eq->id ?? $qId;
                    $marks = 0;
                    $qRecord = DB::table('questions')->where('id', $qId)->first();

                    if ($qRecord && $eq) {
                        if ($qRecord->question_type == 'singlechoice' || $qRecord->question_type == 'true_false') {
                            if ($selected == $qRecord->correct) {
                                $marks = $eq->marks;
                            }
                        } elseif ($qRecord->question_type == 'multichoice') {
                            $selectedArr = is_array($selected) ? $selected : json_decode($selected, true);
                            $correctArr = json_decode($qRecord->correct, true);
                            if (is_array($selectedArr) && is_array($correctArr)) {
                                sort($selectedArr);
                                sort($correctArr);
                                if ($selectedArr == $correctArr) {
                                    $marks = $eq->marks;
                                }
                            }
                        }
                    }

                    DB::table('onlineexam_student_results')->updateOrInsert(
                        [
                            'onlineexam_student_id' => $onlineExamStudentId,
                            'onlineexam_question_id' => $eqId,
                        ],
                        [
                            'select_option' => is_array($selected) ? json_encode($selected) : (string)$selected,
                            'marks' => $marks,
                            'remark' => '',
                            'attachment_name' => '',
                            'attachment_upload_name' => '',
                            'created_at' => now(),
                            'updated_at' => now()->toDateString(),
                        ]
                    );
                }
            }
        }

        $result = null;
        if (Schema::hasTable('online_exam_results')) {
            $studentId = $this->getStudentId($user);
            $result = OnlineExamResult::create([
                'online_exam_id' => $examId,
                'student_id' => $studentId,
                'answers' => json_encode($answers),
                'obtained_marks' => 0,
                'attended_on' => now(),
                'is_active' => 1,
            ]);
        }

        return $this->successResponse(['result' => $result ?? ['exam_id' => $examId, 'submitted' => true]], 'Exam submitted successfully');
    }

    private function getStudentSession($user)
    {
        $studentId = $this->getStudentId($user);

        if (!$studentId) {
            return null;
        }

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->session_id))
            ->first();
    }

    private function getStudentId($user)
    {
        if ($user->role === 'student') {
            return $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            return $student ? $student->id : null;
        }

        return null;
    }
}
