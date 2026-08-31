<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OnlineExamDataSeeder extends Seeder
{
    public function run(): void
    {
        $classId = 1;
        $sectionId = 1;

        $subjectMathId = DB::table('subjects')->where('name', 'Mathematics')->value('id') ?? 9;
        $subjectSciId  = DB::table('subjects')->where('name', 'Science')->value('id') ?? 10;

        $exams = [
            [
                'exam_title'         => 'اختبار الرياضيات المنتصفي 2026',
                'exam_type'          => 'midterm',
                'class_id'           => $classId,
                'section_id'         => $sectionId,
                'subject_id'         => $subjectMathId,
                'duration'           => '60',
                'minimum_percentage' => 50.0,
                'max_attempts'       => 2,
                'is_active'          => 1,
            ],
            [
                'exam_title'         => 'اختبار العلوم المنتصفي 2026',
                'exam_type'          => 'midterm',
                'class_id'           => $classId,
                'section_id'         => $sectionId,
                'subject_id'         => $subjectSciId,
                'duration'           => '45',
                'minimum_percentage' => 40.0,
                'max_attempts'       => 1,
                'is_active'          => 1,
            ],
            [
                'exam_title'         => 'اختبار الرياضيات النهائي 2026',
                'exam_type'          => 'final',
                'class_id'           => $classId,
                'section_id'         => $sectionId,
                'subject_id'         => $subjectMathId,
                'duration'           => '90',
                'minimum_percentage' => 60.0,
                'max_attempts'       => 1,
                'is_active'          => 1,
            ],
            [
                'exam_title'         => 'اختبار العلوم النهائي (مغلق)',
                'exam_type'          => 'final',
                'class_id'           => $classId,
                'section_id'         => $sectionId,
                'subject_id'         => $subjectSciId,
                'duration'           => '90',
                'minimum_percentage' => 50.0,
                'max_attempts'       => 1,
                'is_active'          => 0,
            ],
        ];

        $createdExamIds = [];

        foreach ($exams as $examData) {
            $existId = DB::table('online_exams')
                ->where('exam_title', $examData['exam_title'])
                ->value('id');

            if (!$existId) {
                $existId = DB::table('online_exams')->insertGetId(array_merge($examData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            $createdExamIds[] = $existId;
        }

        $questionSets = [
            $createdExamIds[0] => [1, 2, 3, 4, 5],
            $createdExamIds[1] => [10, 11, 12],
            $createdExamIds[2] => [1, 2, 3, 4, 5, 6],
        ];

        foreach ($questionSets as $examId => $questionIds) {
            foreach ($questionIds as $qId) {
                $exists = DB::table('online_exam_questions')
                    ->where('online_exam_id', $examId)
                    ->where('question_id', $qId)
                    ->exists();

                if (!$exists) {
                    DB::table('online_exam_questions')->insert([
                        'online_exam_id' => $examId,
                        'question_id'    => $qId,
                        'optional'       => 0,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }

        $this->command->info('تم إنشاء بيانات الاختبارات بنجاح!');
        $this->command->info('class_id=' . $classId . ' | section_id=' . $sectionId);
        $this->command->info('عدد الاختبارات النشطة: 3');
        foreach ($createdExamIds as $i => $id) {
            $this->command->info('  - ' . $exams[$i]['exam_title'] . ' (ID: ' . $id . ') is_active=' . $exams[$i]['is_active']);
        }
    }
}
