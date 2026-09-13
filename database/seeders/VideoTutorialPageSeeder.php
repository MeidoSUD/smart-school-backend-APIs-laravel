<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\ClassSection;
use Modules\Operations\Entities\VideoTutorial;

/**
 * Seeds the dataset required by the CodeIgniter page `user/video_tutorial` (index).
 *
 * CI source of truth:
 * - Controller: application/controllers/user/Video_tutorial.php :: index(), getPage(), genratediv()
 * - Model: application/models/Video_tutorial_model.php :: getvideotutorial()
 * - View: application/views/user/video_tutorial/index.php (AJAX getPage, 30 per page,
 *   modal shows title/description/created_by staff name + YouTube embed of video_link)
 * - Admin create reference: application/controllers/admin/Video_tutorial.php :: add()
 *
 * CI query (getvideotutorial): video_tutorial
 *   LEFT JOIN staff ON staff.id = video_tutorial.created_by
 *   JOIN staff_roles ON staff.id = staff_roles.staff_id        <- INNER join
 *   JOIN video_tutorial_class_sections ON video_tutorial_id = video_tutorial.id
 *   JOIN class_sections ON class_sections.id = class_section_id
 *   JOIN classes, sections
 *   WHERE class_sections.class_id = {class_id}
 *     AND class_sections.section_id = {section_id}
 *   GROUP BY video_tutorial_id ORDER BY video_tutorial.id DESC LIMIT 30, offset
 *
 * Dependency order respected here (parents first):
 *   staff (+ staff_roles) > classes/sections/class_sections > video_tutorial >
 *   video_tutorial_class_sections
 *
 * Only video_tutorial + pivot rows are created; all parent/reference rows are
 * reused via lookups (a missing class_section for an enrolled class/section is
 * created so the student page is not empty). Safe to run repeatedly
 * (updateOrCreate / firstOrCreate, no truncate, no deletes).
 */
class VideoTutorialPageSeeder extends Seeder
{
    public function run(): void
    {
        // ---- 1. Teaching staff: must have a staff_roles row, otherwise the CI
        //        INNER JOIN staff_roles filters the video out of the student page.
        //        Prefer the known teacher; never use a role_id 7 (superadmin)
        //        creator so the staff name stays visible under superadmin_restriction.
        $teacher = DB::table('staff')->where('employee_id', 'TCH2024001')->first();

        if (! $teacher || ! DB::table('staff_roles')->where('staff_id', $teacher->id)->exists()) {
            $teacherId = DB::table('staff_roles')
                ->where('role_id', '!=', 7)
                ->orderBy('staff_id')
                ->value('staff_id');
            $teacher = $teacherId ? DB::table('staff')->where('id', $teacherId)->first() : null;
        }

        if (! $teacher) {
            $teacher = DB::table('staff')->first();
        }

        if (! $teacher) {
            return;
        }

        // ---- 2. Class sections that actually have enrolled students in the
        //        current session. Create the missing link row (not students).
        $sessionId = DB::table('sch_settings')->value('session_id')
            ?? DB::table('sessions')->where('is_active', 'yes')->value('id');

        $enrolledPairs = DB::table('student_session')
            ->when($sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->select('class_id', 'section_id')
            ->groupBy('class_id', 'section_id')
            ->get();

        if ($enrolledPairs->isEmpty()) {
            return;
        }

        $classSectionIds = [];
        foreach ($enrolledPairs as $pair) {
            $cs = ClassSection::firstOrCreate(
                ['class_id' => $pair->class_id, 'section_id' => $pair->section_id],
                ['is_active' => 'yes']
            );
            $classSectionIds[] = $cs->id;
        }

        // ---- 3. Video catalog (8 records: enough for a meaningful grid, below
        //        the 30-per-page pagination threshold so all show on page 1).
        //        Paths mirror admin add(): uploads/video_tutorial/youtube_video/.
        $videos = [
            [
                'title' => 'شرح الأعداد الصحيحة - الرياضيات',
                'vid_title' => 'Integers Explained - Mathematics',
                'description' => 'شرح مفصل لجمع وطرح وضرب وقسمة الأعداد الصحيحة مع أمثلة وتمارين محلولة.',
                'video_link' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'img_name' => 'integers_maths.jpg',
                'thumb_name' => 'integers_maths_thumb.jpg',
            ],
            [
                'title' => 'الخلية ومكوناتها - العلوم',
                'vid_title' => 'The Cell and Its Components - Science',
                'description' => 'درس مرئي يوضح مكونات الخلية النباتية والحيوانية والفرق بينهما.',
                'video_link' => 'https://www.youtube.com/watch?v=8IlzKri08kk',
                'img_name' => 'cell_science.jpg',
                'thumb_name' => 'cell_science_thumb.jpg',
            ],
            [
                'title' => 'الفاعل وإعرابه - اللغة العربية',
                'vid_title' => 'Arabic Grammar: The Subject (Al-Fail)',
                'description' => 'شرح قاعدة الفاعل وعلامات إعرابه مع جمل تطبيقية من المنهج.',
                'video_link' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'img_name' => 'arabic_fail.jpg',
                'thumb_name' => 'arabic_fail_thumb.jpg',
            ],
            [
                'title' => 'Present Simple Tense - English',
                'vid_title' => 'Present Simple: Usage and Form',
                'description' => 'Video lesson explaining present simple usage, form, negatives and questions with practice exercises.',
                'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'img_name' => 'present_simple.jpg',
                'thumb_name' => 'present_simple_thumb.jpg',
            ],
            [
                'title' => 'السيرة النبوية: الهجرة - التربية الإسلامية',
                'vid_title' => 'Prophet Biography: The Hijrah',
                'description' => 'عرض مرئي لأحداث الهجرة النبوية ودروسها المستفادة.',
                'video_link' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'img_name' => 'hijrah_isl.jpg',
                'thumb_name' => 'hijrah_isl_thumb.jpg',
            ],
            [
                'title' => 'قراءة الخريطة وعناصرها - الاجتماعيات',
                'vid_title' => 'Map Reading and Elements - Social Studies',
                'description' => 'شرح عناصر الخريطة وكيفية قراءتها وتحديد الاتجاهات والرموز.',
                'video_link' => 'https://www.youtube.com/watch?v=eKFTSSKCzWA',
                'img_name' => 'map_soc.jpg',
                'thumb_name' => 'map_soc_thumb.jpg',
            ],
            [
                'title' => 'أساسيات الحاسب وأنظمة التشغيل',
                'vid_title' => 'Computer Basics and Operating Systems',
                'description' => 'جولة مرئية في مكونات الحاسب المادية والبرمجية وأنظمة التشغيل.',
                'video_link' => 'https://www.youtube.com/watch?v=60ItHLz5WEA',
                'img_name' => 'computer_cs.jpg',
                'thumb_name' => 'computer_cs_thumb.jpg',
            ],
            [
                'title' => 'مراجعة شاملة ليلة الامتحان - الرياضيات',
                'vid_title' => 'Final Exam Night Review - Mathematics',
                'description' => 'مراجعة مركزة لأهم قواعد الفصل مع حل نماذج امتحانات سابقة.',
                'video_link' => 'https://www.youtube.com/watch?v=kJQP7kiw5Fk',
                'img_name' => 'exam_review.jpg',
                'thumb_name' => 'exam_review_thumb.jpg',
            ],
        ];

        // ---- 4. Class-section assignment per video (many-to-many combos so every
        //        enrolled class/section shows 2+ videos, some videos shared).
        $csCount = count($classSectionIds);
        $assign = [
            0 => [0],
            1 => [0, 1 % $csCount],
            2 => [0, 1 % $csCount],
            3 => [1 % $csCount, 4 % $csCount],
            4 => [2 % $csCount, 4 % $csCount],
            5 => [2 % $csCount, 3 % $csCount],
            6 => [3 % $csCount, 4 % $csCount],
            7 => [0, 2 % $csCount, 3 % $csCount, 4 % $csCount],
        ];

        foreach ($videos as $index => $video) {
            $record = VideoTutorial::updateOrCreate(
                ['video_link' => $video['video_link']],
                [
                    'title' => $video['title'],
                    'vid_title' => $video['vid_title'],
                    'description' => $video['description'],
                    'thumb_path' => 'uploads/video_tutorial/youtube_video/thumb/',
                    'dir_path' => 'uploads/video_tutorial/youtube_video/',
                    'img_name' => $video['img_name'],
                    'thumb_name' => $video['thumb_name'],
                    'created_by' => $teacher->id,
                ]
            );

            $targets = array_unique(array_map(
                fn ($i) => $classSectionIds[$i % $csCount],
                $assign[$index] ?? [0]
            ));

            foreach ($targets as $classSectionId) {
                DB::table('video_tutorial_class_sections')->updateOrInsert(
                    [
                        'video_tutorial_id' => $record->id,
                        'class_section_id' => $classSectionId,
                    ],
                    ['created_date' => now()->format('Y-m-d')]
                );
            }
        }
    }
}
