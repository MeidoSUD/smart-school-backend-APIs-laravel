<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentExamSeeder extends Seeder
{
    public function run(): void
    {
        // Create an exam group
        $examGroupId = DB::table('exam_groups')->insertGetId([
            'name' => 'Midterm Exams',
            'exam_type' => 'general',
            'description' => 'Midterm examination',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get a valid session id
        $sessionId = DB::table('sessions')->inRandomOrder()->value('id') ?? 1;

        // Create an exam batch
        $examBatchId = DB::table('exam_group_class_batch_exams')->insertGetId([
            'exam' => 'Math Midterm',
            'passing_percentage' => 50,
            'session_id' => $sessionId,
            'date_from' => now()->subDays(10),
            'date_to' => now()->subDays(9),
            'exam_group_id' => $examGroupId,
            'use_exam_roll_no' => 0,
            'is_publish' => 1,
            'is_rank_generated' => 0,
            'description' => 'Math Midterm Exam',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentSessions = DB::table('student_session')->take(10)->get();

        foreach ($studentSessions as $session) {
            // Create exam group student
            $examGroupStudentId = DB::table('exam_group_students')->insertGetId([
                'exam_group_id' => $examGroupId,
                'student_id' => $session->student_id,
                'student_session_id' => $session->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign student to the exam batch
            $studentExamId = DB::table('exam_group_class_batch_exam_students')->insertGetId([
                'exam_group_class_batch_exam_id' => $examBatchId,
                'student_id' => $session->student_id,
                'student_session_id' => $session->id,
                'roll_no' => rand(1000, 9999),
                'teacher_remark' => 'Good',
                'rank' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add results for the student
            DB::table('exam_group_exam_results')->insert([
                'exam_group_class_batch_exam_student_id' => $studentExamId,
                'exam_group_class_batch_exam_subject_id' => 1, // Assuming subject 1 exists, safe fallback
                'exam_group_student_id' => $examGroupStudentId,
                'attendence' => 'present',
                'get_marks' => rand(50, 100),
                'note' => 'Passed',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
