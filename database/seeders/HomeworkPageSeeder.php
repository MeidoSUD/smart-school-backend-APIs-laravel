<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class HomeworkPageSeeder extends Seeder
{
    public function run(): void
    {
        $sessionId = DB::table('sessions')->where('is_active', 'yes')->value('id') ?? 28;
        $staffId = DB::table('staff')->value('id') ?? 1;

        $classes = DB::table('classes')->get();
        $classId1 = $classes->first()->id ?? 1;
        $classId2 = $classes->count() > 1 ? $classes->skip(1)->first()->id : $classId1;

        $sections = DB::table('sections')->get();
        $sectionId1 = $sections->first()->id ?? 1;
        $sectionId2 = $sections->count() > 1 ? $sections->skip(1)->first()->id : $sectionId1;
        
        $subjectGroupSubject1 = DB::table('subject_group_subjects')->first();
        $subjectGroupSubjectId = $subjectGroupSubject1->id ?? 1;
        $subjectId = $subjectGroupSubject1->subject_id ?? 1;

        Schema::disableForeignKeyConstraints();

        DB::table('submit_assignment')->truncate();
        DB::table('homework_evaluation')->truncate();
        DB::table('homework')->truncate();

        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();
        
        $homeworks = [
            [
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'session_id' => $sessionId,
                'staff_id' => $staffId,
                'subject_group_subject_id' => $subjectGroupSubjectId,
                'subject_id' => $subjectId,
                'homework_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'submission_date' => $now->copy()->subDays(1)->format('Y-m-d'),
                'submit_date' => $now->copy()->subDays(1)->format('Y-m-d'),
                'marks' => 10.00,
                'description' => 'حل التمارين في الصفحة 45 من كتاب الرياضيات.',
                'create_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'evaluation_date' => $now->copy()->format('Y-m-d'),
                'document' => '',
                'created_by' => $staffId,
                'evaluated_by' => $staffId,
                'created_at' => $now,
            ],
            [
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'session_id' => $sessionId,
                'staff_id' => $staffId,
                'subject_group_subject_id' => $subjectGroupSubjectId,
                'subject_id' => $subjectId,
                'homework_date' => $now->copy()->addDays(1)->format('Y-m-d'),
                'submission_date' => $now->copy()->addDays(3)->format('Y-m-d'),
                'submit_date' => $now->copy()->addDays(3)->format('Y-m-d'),
                'marks' => 20.00,
                'description' => 'مراجعة الفصل الثاني استعدادا للاختبار.',
                'create_date' => $now->copy()->format('Y-m-d'),
                'evaluation_date' => null,
                'document' => '',
                'created_by' => $staffId,
                'evaluated_by' => null,
                'created_at' => $now,
            ],
            [
                'class_id' => $classId2,
                'section_id' => $sectionId2,
                'session_id' => $sessionId,
                'staff_id' => $staffId,
                'subject_group_subject_id' => $subjectGroupSubjectId,
                'subject_id' => $subjectId,
                'homework_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'submission_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'submit_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'marks' => 15.00,
                'description' => 'كتابة مقال عن البيئة.',
                'create_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'evaluation_date' => $now->copy()->subDays(2)->format('Y-m-d'),
                'document' => '',
                'created_by' => $staffId,
                'evaluated_by' => $staffId,
                'created_at' => $now->copy()->subDays(10),
            ]
        ];
        
        $studentSessions = DB::table('student_session')->where('is_active', 'yes')->get();
        
        foreach ($homeworks as $hwData) {
            $targetClass = $hwData['class_id'];
            $targetSection = $hwData['section_id'];
            
            $hwId = DB::table('homework')->insertGetId($hwData);
            
            // Get students for this class and section
            $studentsForHw = $studentSessions->filter(function($ss) use ($targetClass, $targetSection) {
                return $ss->class_id == $targetClass && $ss->section_id == $targetSection;
            });
            
            if ($hwData['evaluation_date']) {
                foreach ($studentsForHw as $ss) {
                    $status = rand(0, 1) ? 'Complete' : 'Incomplete';
                    
                    if ($status === 'Complete') {
                        DB::table('submit_assignment')->insert([
                            'homework_id' => $hwId,
                            'student_id' => $ss->student_id,
                            'message' => 'تم تسليم الواجب بنجاح.',
                            'docs' => '',
                        ]);
                    }
                    
                    DB::table('homework_evaluation')->insert([
                        'homework_id' => $hwId,
                        'student_id' => $ss->student_id,
                        'student_session_id' => $ss->id,
                        'marks' => $status === 'Complete' ? rand(5, $hwData['marks']) : 0,
                        'note' => $status === 'Complete' ? 'عمل ممتاز' : 'لم يتم التسليم',
                        'date' => $hwData['evaluation_date'],
                        'status' => $status,
                    ]);
                }
            } else {
                // Not evaluated yet, but some might have submitted
                foreach ($studentsForHw as $ss) {
                    if (rand(0, 1)) {
                        DB::table('submit_assignment')->insert([
                            'homework_id' => $hwId,
                            'student_id' => $ss->student_id,
                            'message' => 'تم التسليم.',
                            'docs' => '',
                        ]);
                    }
                }
            }
        }
    }
}
