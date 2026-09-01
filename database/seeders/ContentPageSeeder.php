<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Entities\Staff;
use Modules\Academic\Entities\ClassSection;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Entities\User;

class ContentPageSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('share_contents')->exists()) {
            return;
        }

        $teacher = Staff::where('employee_id', 'TCH2024001')->first();
        if (!$teacher) {
            $teacher = Staff::where('user_id', '!=', 0)->first();
        }
        if (!$teacher) {
            return;
        }

        // ===== Content Types =====
        $contentTypes = [
            ['name' => 'Study Material', 'description' => 'Educational study materials', 'is_active' => 1, 'created_at' => now()],
            ['name' => 'Assignments', 'description' => 'Student assignments', 'is_active' => 1, 'created_at' => now()],
            ['name' => 'Syllabus', 'description' => 'Course syllabus', 'is_active' => 1, 'created_at' => now()],
            ['name' => 'Other Downloads', 'description' => 'Other downloadable content', 'is_active' => 1, 'created_at' => now()],
        ];

        foreach ($contentTypes as $ct) {
            DB::table('content_types')->updateOrInsert(
                ['name' => $ct['name']],
                $ct
            );
        }

        $studyMaterialType = DB::table('content_types')->where('name', 'Study Material')->first();
        $assignmentType = DB::table('content_types')->where('name', 'Assignments')->first();
        $syllabusType = DB::table('content_types')->where('name', 'Syllabus')->first();
        $otherType = DB::table('content_types')->where('name', 'Other Downloads')->first();

        // ===== Upload Contents =====
        $uploadContents = [
            [
                'content_type_id' => $studyMaterialType->id,
                'real_name' => 'math_chapter1_notes.pdf',
                'image' => '',
                'thumb_path' => '',
                'dir_path' => 'uploads/content/study_material/',
                'img_name' => 'math_chapter1_notes.pdf',
                'thumb_name' => '',
                'file_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => '2048000',
                'vid_url' => '',
                'vid_title' => '',
                'upload_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'content_type_id' => $studyMaterialType->id,
                'real_name' => 'arabic_grammar_lesson1.pdf',
                'image' => '',
                'thumb_path' => '',
                'dir_path' => 'uploads/content/study_material/',
                'img_name' => 'arabic_grammar_lesson1.pdf',
                'thumb_name' => '',
                'file_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => '1536000',
                'vid_url' => '',
                'vid_title' => '',
                'upload_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'content_type_id' => $assignmentType->id,
                'real_name' => 'science_homework_week1.pdf',
                'image' => '',
                'thumb_path' => '',
                'dir_path' => 'uploads/content/assignments/',
                'img_name' => 'science_homework_week1.pdf',
                'thumb_name' => '',
                'file_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => '1024000',
                'vid_url' => '',
                'vid_title' => '',
                'upload_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'content_type_id' => $syllabusType->id,
                'real_name' => 'semester1_syllabus.pdf',
                'image' => '',
                'thumb_path' => '',
                'dir_path' => 'uploads/content/syllabus/',
                'img_name' => 'semester1_syllabus.pdf',
                'thumb_name' => '',
                'file_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => '3072000',
                'vid_url' => '',
                'vid_title' => '',
                'upload_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'content_type_id' => $otherType->id,
                'real_name' => 'school_calendar_2025.pdf',
                'image' => '',
                'thumb_path' => '',
                'dir_path' => 'uploads/content/other/',
                'img_name' => 'school_calendar_2025.pdf',
                'thumb_name' => '',
                'file_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => '512000',
                'vid_url' => '',
                'vid_title' => '',
                'upload_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'content_type_id' => $studyMaterialType->id,
                'real_name' => 'english_vocabulary_unit2.mp4',
                'image' => '',
                'thumb_path' => '',
                'dir_path' => 'uploads/content/study_material/',
                'img_name' => 'english_vocabulary_unit2.mp4',
                'thumb_name' => '',
                'file_type' => 'video',
                'mime_type' => 'video/mp4',
                'file_size' => '52428800',
                'vid_url' => 'https://www.youtube.com/watch?v=example',
                'vid_title' => 'English Vocabulary Unit 2',
                'upload_by' => $teacher->id,
                'created_at' => now(),
            ],
        ];

        $uploadedIds = [];
        foreach ($uploadContents as $uc) {
            $id = DB::table('upload_contents')->insertGetId($uc);
            $uploadedIds[] = $id;
        }

        // ===== Share Contents =====
        $classSections = ClassSection::all();
        $studentSessions = StudentSession::all();
        $parentUser = User::where('role', 'parent')->first();

        $shareContents = [
            [
                'send_to' => 'student',
                'title' => 'Mathematics Chapter 1 Study Material',
                'share_date' => now()->subDays(5)->format('Y-m-d'),
                'valid_upto' => now()->addMonth()->format('Y-m-d'),
                'description' => 'Important study material for Mathematics Chapter 1. Please review before the exam.',
                'created_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'send_to' => 'student',
                'title' => 'Arabic Grammar Lesson 1',
                'share_date' => now()->subDays(3)->format('Y-m-d'),
                'valid_upto' => now()->addDays(15)->format('Y-m-d'),
                'description' => 'Arabic grammar notes for Lesson 1. Complete exercises before class.',
                'created_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'send_to' => 'student',
                'title' => 'Science Homework - Week 1',
                'share_date' => now()->subDays(2)->format('Y-m-d'),
                'valid_upto' => now()->addWeek()->format('Y-m-d'),
                'description' => 'Science homework assignment for Week 1. Submit by the due date.',
                'created_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'send_to' => 'student',
                'title' => 'First Semester Syllabus',
                'share_date' => now()->subDays(10)->format('Y-m-d'),
                'valid_upto' => now()->addMonths(3)->format('Y-m-d'),
                'description' => 'Complete syllabus for the first semester. Review all subjects.',
                'created_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'send_to' => 'student',
                'title' => 'School Calendar 2025',
                'share_date' => now()->subDay()->format('Y-m-d'),
                'valid_upto' => now()->addMonths(6)->format('Y-m-d'),
                'description' => 'Academic calendar with important dates and holidays.',
                'created_by' => $teacher->id,
                'created_at' => now(),
            ],
            [
                'send_to' => 'student',
                'title' => 'English Vocabulary Video Lesson',
                'share_date' => now()->format('Y-m-d'),
                'valid_upto' => now()->addMonth()->format('Y-m-d'),
                'description' => 'Watch this video lesson for English Vocabulary Unit 2.',
                'created_by' => $teacher->id,
                'created_at' => now(),
            ],
        ];

        $shareIds = [];
        foreach ($shareContents as $sc) {
            $id = DB::table('share_contents')->insertGetId($sc);
            $shareIds[] = $id;
        }

        // ===== Share Content For (Pivot) =====
        $shareContentForRecords = [];

        // Share 1: To all students (group_id = 'student')
        $shareContentForRecords[] = [
            'share_content_id' => $shareIds[0],
            'group_id' => 'student',
            'student_id' => null,
            'user_parent_id' => null,
            'staff_id' => null,
            'class_section_id' => null,
        ];

        // Share 2: To specific class sections
        if ($classSections->isNotEmpty()) {
            $shareContentForRecords[] = [
                'share_content_id' => $shareIds[1],
                'group_id' => null,
                'student_id' => null,
                'user_parent_id' => null,
                'staff_id' => null,
                'class_section_id' => $classSections->first()->id,
            ];
        }

        // Share 3: To specific students
        if ($studentSessions->isNotEmpty()) {
            $shareContentForRecords[] = [
                'share_content_id' => $shareIds[2],
                'group_id' => null,
                'student_id' => $studentSessions->first()->student_id,
                'user_parent_id' => null,
                'staff_id' => null,
                'class_section_id' => null,
            ];
        }

        // Share 4: To all parents (group_id = 'parent')
        $shareContentForRecords[] = [
            'share_content_id' => $shareIds[3],
            'group_id' => 'parent',
            'student_id' => null,
            'user_parent_id' => null,
            'staff_id' => null,
            'class_section_id' => null,
        ];

        // Share 5: To specific parent
        if ($parentUser) {
            $shareContentForRecords[] = [
                'share_content_id' => $shareIds[4],
                'group_id' => null,
                'student_id' => null,
                'user_parent_id' => $parentUser->id,
                'staff_id' => null,
                'class_section_id' => null,
            ];
        }

        // Share 6: To class section
        if ($classSections->isNotEmpty()) {
            $shareContentForRecords[] = [
                'share_content_id' => $shareIds[5],
                'group_id' => null,
                'student_id' => null,
                'user_parent_id' => null,
                'staff_id' => null,
                'class_section_id' => $classSections->first()->id,
            ];
        }

        foreach ($shareContentForRecords as $scf) {
            DB::table('share_content_for')->insert($scf);
        }

        // ===== Share Upload Contents (Pivot) =====
        $shareUploadMappings = [
            // Share 1 -> Upload 1 (Math study material)
            ['share_content_id' => $shareIds[0], 'upload_content_id' => $uploadedIds[0]],
            // Share 2 -> Upload 2 (Arabic grammar)
            ['share_content_id' => $shareIds[1], 'upload_content_id' => $uploadedIds[1]],
            // Share 3 -> Upload 3 (Science homework)
            ['share_content_id' => $shareIds[2], 'upload_content_id' => $uploadedIds[2]],
            // Share 4 -> Upload 4 (Syllabus)
            ['share_content_id' => $shareIds[3], 'upload_content_id' => $uploadedIds[3]],
            // Share 5 -> Upload 5 (School calendar)
            ['share_content_id' => $shareIds[4], 'upload_content_id' => $uploadedIds[4]],
            // Share 6 -> Upload 6 (English video)
            ['share_content_id' => $shareIds[5], 'upload_content_id' => $uploadedIds[5]],
        ];

        foreach ($shareUploadMappings as $sum) {
            DB::table('share_upload_contents')->insert($sum);
        }

        // ===== Additional Contents (for the contents table used by Laravel controller) =====
        if (DB::table('contents')->count() < 6) {
            $additionalContents = [
                [
                    'title' => 'Mathematics Chapter 1 Notes',
                    'type' => 'study_material',
                    'is_public' => 'Yes',
                    'class_id' => $classSections->first()->class_id ?? 1,
                    'cls_sec_id' => $classSections->first()->id ?? 1,
                    'file' => 'math_chapter1_notes.pdf',
                    'date' => now()->subDays(5)->format('Y-m-d'),
                    'note' => 'Comprehensive notes for Mathematics Chapter 1',
                    'is_active' => 'yes',
                    'created_by' => $teacher->id,
                    'created_at' => now(),
                ],
                [
                    'title' => 'Arabic Grammar Lesson 1',
                    'type' => 'study_material',
                    'is_public' => 'Yes',
                    'class_id' => $classSections->first()->class_id ?? 1,
                    'cls_sec_id' => $classSections->first()->id ?? 1,
                    'file' => 'arabic_grammar_lesson1.pdf',
                    'date' => now()->subDays(3)->format('Y-m-d'),
                    'note' => 'Arabic grammar notes for Lesson 1',
                    'is_active' => 'yes',
                    'created_by' => $teacher->id,
                    'created_at' => now(),
                ],
                [
                    'title' => 'Science Homework Week 1',
                    'type' => 'assignments',
                    'is_public' => 'No',
                    'class_id' => $classSections->first()->class_id ?? 1,
                    'cls_sec_id' => $classSections->first()->id ?? 1,
                    'file' => 'science_homework_week1.pdf',
                    'date' => now()->subDays(2)->format('Y-m-d'),
                    'note' => 'Science homework assignment for Week 1',
                    'is_active' => 'yes',
                    'created_by' => $teacher->id,
                    'created_at' => now(),
                ],
                [
                    'title' => 'First Semester Syllabus',
                    'type' => 'syllabus',
                    'is_public' => 'Yes',
                    'class_id' => $classSections->first()->class_id ?? 1,
                    'cls_sec_id' => $classSections->first()->id ?? 1,
                    'file' => 'semester1_syllabus.pdf',
                    'date' => now()->subDays(10)->format('Y-m-d'),
                    'note' => 'Complete syllabus for first semester',
                    'is_active' => 'yes',
                    'created_by' => $teacher->id,
                    'created_at' => now(),
                ],
                [
                    'title' => 'School Calendar 2025',
                    'type' => 'other_download',
                    'is_public' => 'Yes',
                    'class_id' => $classSections->first()->class_id ?? 1,
                    'cls_sec_id' => $classSections->first()->id ?? 1,
                    'file' => 'school_calendar_2025.pdf',
                    'date' => now()->subDay()->format('Y-m-d'),
                    'note' => 'Academic calendar for 2025',
                    'is_active' => 'yes',
                    'created_by' => $teacher->id,
                    'created_at' => now(),
                ],
                [
                    'title' => 'English Vocabulary Video',
                    'type' => 'study_material',
                    'is_public' => 'No',
                    'class_id' => $classSections->first()->class_id ?? 1,
                    'cls_sec_id' => $classSections->first()->id ?? 1,
                    'file' => 'english_vocabulary_unit2.mp4',
                    'date' => now()->format('Y-m-d'),
                    'note' => 'Video lesson for English Vocabulary Unit 2',
                    'is_active' => 'yes',
                    'created_by' => $teacher->id,
                    'created_at' => now(),
                ],
            ];

            foreach ($additionalContents as $ac) {
                DB::table('contents')->insert($ac);
            }
        }
    }
}
