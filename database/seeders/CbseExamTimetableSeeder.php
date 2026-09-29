<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds data required by CodeIgniter page: user/cbse/exam/timetable
 * (User\CBE\Exam::timetable -> Cbseexam_exam_model::getStudentExamTimetable -> user/cbse/timetable view).
 *
 * Page displays per assigned exam: exam name + timetable rows
 * (subject_name + code, date, time_from, duration, room_no).
 */
class CbseExamTimetableSeeder extends Seeder
{
    public function run(): void
    {
        // Active session is the source of truth (CI uses setting_model->getCurrentSession).
        $sessionId = DB::table('sessions')->where('is_active', 'yes')->value('id')
            ?? DB::table('sessions')->orderByDesc('id')->value('id');

        if (!$sessionId) {
            return;
        }

        $subjects = DB::table('subjects')->orderBy('id')->get(['id', 'name', 'code']);
        if ($subjects->isEmpty()) {
            return;
        }

        $studentSessions = DB::table('student_session')
            ->where('session_id', $sessionId)
            ->orderBy('id')
            ->get(['id', 'class_id', 'section_id']);

        if ($studentSessions->isEmpty()) {
            return;
        }

        // 1. Terms (cbse_terms.term_code is the natural key).
        $term1Id = $this->firstOrInsert('cbse_terms', ['term_code' => 'TERM1'], [
            'name' => 'Term 1',
            'term_code' => 'TERM1',
            'description' => 'CBSE Term 1',
        ]);
        $term2Id = $this->firstOrInsert('cbse_terms', ['term_code' => 'TERM2'], [
            'name' => 'Term 2',
            'term_code' => 'TERM2',
            'description' => 'CBSE Term 2',
        ]);

        // 2. Assessment + assessment types.
        $assessmentId = $this->firstOrInsert('cbse_exam_assessments', ['name' => 'Summative Assessment'], [
            'name' => 'Summative Assessment',
            'description' => 'Main written + internal assessment used by CBSE exams',
        ]);

        $writtenTypeId = $this->firstOrInsert(
            'cbse_exam_assessment_types',
            ['cbse_exam_assessment_id' => $assessmentId, 'code' => 'WR'],
            [
                'cbse_exam_assessment_id' => $assessmentId,
                'name' => 'Written',
                'code' => 'WR',
                'maximum_marks' => 80,
                'pass_percentage' => 33,
                'description' => 'Written examination',
            ]
        );
        $internalTypeId = $this->firstOrInsert(
            'cbse_exam_assessment_types',
            ['cbse_exam_assessment_id' => $assessmentId, 'code' => 'IN'],
            [
                'cbse_exam_assessment_id' => $assessmentId,
                'name' => 'Internal Assessment',
                'code' => 'IN',
                'maximum_marks' => 20,
                'pass_percentage' => 33,
                'description' => 'Internal assessment',
            ]
        );

        // 3. Exams (CI timetable query requires session_id = current + is_active = 1).
        $exams = [
            [
                'name' => 'CBSE Term 1 Examination',
                'exam_code' => 'CBSE-T1',
                'cbse_term_id' => $term1Id,
            ],
            [
                'name' => 'CBSE Term 2 Examination',
                'exam_code' => 'CBSE-T2',
                'cbse_term_id' => $term2Id,
            ],
        ];

        $examIds = [];
        foreach ($exams as $exam) {
            $examIds[] = $this->firstOrInsert(
                'cbse_exams',
                ['exam_code' => $exam['exam_code'], 'session_id' => $sessionId],
                [
                    'total_working_days' => 0,
                    'cbse_term_id' => $exam['cbse_term_id'],
                    'cbse_exam_assessment_id' => $assessmentId,
                    'cbse_exam_grade_id' => null,
                    'name' => $exam['name'],
                    'exam_code' => $exam['exam_code'],
                    'session_id' => $sessionId,
                    'description' => $exam['name'] . ' for current session',
                    'is_publish' => 1,
                    'is_active' => 1,
                    'use_exam_roll_no' => 0,
                ]
            );
        }

        // 4. Exam -> class_section links (cover the class_sections of seeded students).
        $classSectionIds = DB::table('class_sections')->orderBy('id')->pluck('id')->all();
        $studentClassSectionIds = [];
        foreach ($studentSessions as $ss) {
            $csId = DB::table('class_sections')
                ->where('class_id', $ss->class_id)
                ->where('section_id', $ss->section_id)
                ->value('id');
            if ($csId) {
                $studentClassSectionIds[$csId] = $csId;
            }
        }
        if (empty($studentClassSectionIds) && !empty($classSectionIds)) {
            $studentClassSectionIds = [$classSectionIds[0] => $classSectionIds[0]];
        }

        // Exam 1 -> all student class_sections, Exam 2 -> first one (enough for pagination/filter demo).
        $examClassMap = [
            $examIds[0] => array_values($studentClassSectionIds),
            $examIds[1] => [array_values($studentClassSectionIds)[0]],
        ];
        foreach ($examClassMap as $examId => $csIds) {
            foreach ($csIds as $csId) {
                $exists = DB::table('cbse_exam_class_sections')
                    ->where('cbse_exam_id', $examId)
                    ->where('class_section_id', $csId)
                    ->exists();
                if (!$exists) {
                    DB::table('cbse_exam_class_sections')->insert([
                        'cbse_exam_id' => $examId,
                        'class_section_id' => $csId,
                    ]);
                }
            }
        }

        // 5. Timetable rows: 6 subjects per exam with consecutive dates.
        $timetableSubjects = $subjects->take(6)->values();
        $baseDate = now()->addDays(7)->startOfDay();

        foreach ($examIds as $examIndex => $examId) {
            foreach ($timetableSubjects as $i => $subject) {
                $date = $baseDate->copy()->addDays($examIndex * 10 + $i)->format('Y-m-d');
                $exists = DB::table('cbse_exam_timetable')
                    ->where('cbse_exam_id', $examId)
                    ->where('subject_id', $subject->id)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $timetableId = DB::table('cbse_exam_timetable')->insertGetId([
                    'cbse_exam_id' => $examId,
                    'subject_id' => $subject->id,
                    'date' => $date,
                    'time_from' => '09:00:00',
                    'time_to' => '12:00:00',
                    'duration' => 180,
                    'room_no' => 'H-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'is_written' => 1,
                    'written_maximum_marks' => 80,
                    'is_practical' => 0,
                    'practical_maximum_mark' => null,
                ]);

                // Link assessment types so result/report pages stay coherent.
                foreach ([$writtenTypeId, $internalTypeId] as $typeId) {
                    $linkExists = DB::table('cbse_exam_timetable_assessment_types')
                        ->where('cbse_exam_timetable_id', $timetableId)
                        ->where('cbse_exam_assessment_type_id', $typeId)
                        ->exists();
                    if (!$linkExists) {
                        DB::table('cbse_exam_timetable_assessment_types')->insert([
                            'cbse_exam_timetable_id' => $timetableId,
                            'cbse_exam_assessment_type_id' => $typeId,
                        ]);
                    }
                }
            }
        }

        // 6. Assign students to exams (drives getStudentExamTimetable output).
        foreach ($examIds as $examId) {
            foreach ($studentSessions as $ss) {
                $exists = DB::table('cbse_exam_students')
                    ->where('cbse_exam_id', $examId)
                    ->where('student_session_id', $ss->id)
                    ->exists();
                if ($exists) {
                    continue;
                }

                DB::table('cbse_exam_students')->insert([
                    'cbse_exam_id' => $examId,
                    'student_session_id' => $ss->id,
                    'staff_id' => null,
                    'roll_no' => null,
                    'remark' => null,
                    'total_present_days' => null,
                    'delete_student_id' => 0,
                ]);
            }
        }
    }

    private function firstOrInsert(string $table, array $unique, array $values): int
    {
        $existing = DB::table($table)->where($unique)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table($table)->insertGetId($values);
    }
}
