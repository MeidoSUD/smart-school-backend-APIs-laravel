<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\LessonPlanTopic;
use Modules\Academic\Entities\Syllabus;
use Modules\Academic\Entities\SyllabusMessage;

/**
 * Seeds the dataset required by the CodeIgniter page `user/syllabus` (index).
 *
 * CI source of truth:
 * - Controller: application/controllers/user/Syllabus.php :: index(), get_weekdates(),
 *   get_subject_syllabus(), addmessage(), getmessage(), status()
 * - Models: Syllabus_model (get_studentsyllabus, get_subject_syllabus_student_byDate,
 *   get_subject_syllabus_student, getmysubjects, get_subjectstatus,
 *   get_subjectsyllabussreport, get_topicbylessonid, addmessage, getstudentmessage),
 *   Lessonplan_model (lesson/topic/subject_syllabus CRUD)
 * - Views: user/syllabus/syllabus.php (AJAX week grid), _get_weekdates.php
 *   (one cell per day -> get_subject_syllabus_student_byDate), _get_subject_syllabus.php
 *   (lesson detail + comments), _get_message.php (lesson_plan_forum list)
 *
 * Dependency order respected here (parents first):
 *   sessions/sch_settings > classes/sections/class_sections > subjects/subject_groups/
 *   subject_group_subjects/subject_group_class_sections > students/student_session >
 *   staff > lesson > topic > subject_syllabus > lesson_plan_forum
 *
 * Only lesson/topic/subject_syllabus/lesson_plan_forum rows are created; all
 * parent/reference rows are reused via firstOrCreate-free lookups (no duplicates).
 * Safe to run repeatedly (firstOrCreate / updateOrInsert, no truncate).
 */
class SyllabusPageSeeder extends Seeder
{
    public function run(): void
    {
        // ---- 1. Current session (sch_settings.session_id is source of truth) ----
        $sessionId = DB::table('sch_settings')->value('session_id')
            ?? DB::table('sessions')->where('is_active', 'yes')->value('id')
            ?? DB::table('sessions')->value('id');

        if (! $sessionId) {
            return;
        }

        // ---- 2. Target class/section: the one with the most enrolled students ----
        $target = DB::table('student_session')
            ->where('session_id', $sessionId)
            ->select('class_id', 'section_id', DB::raw('COUNT(*) as total'))
            ->groupBy('class_id', 'section_id')
            ->orderByDesc('total')
            ->first();

        if (! $target) {
            return;
        }

        $classSection = DB::table('class_sections')
            ->where('class_id', $target->class_id)
            ->where('section_id', $target->section_id)
            ->first();

        if (! $classSection) {
            return;
        }

        // ---- 3. Subject-group link for this class section + current session ----
        $sgcs = DB::table('subject_group_class_sections')
            ->where('class_section_id', $classSection->id)
            ->where('session_id', $sessionId)
            ->first();

        if (! $sgcs) {
            return;
        }

        $subjectGroupSubjects = DB::table('subject_group_subjects')
            ->where('subject_group_id', $sgcs->subject_group_id)
            ->where('session_id', $sessionId)
            ->orderBy('id')
            ->get();

        if ($subjectGroupSubjects->isEmpty()) {
            return;
        }

        // ---- 4. Teaching staff (subject_syllabus.created_by/created_for are NOT NULL) ----
        $teacher = DB::table('staff')->where('employee_id', 'TCH2024001')->first()
            ?? DB::table('staff')->first();

        if (! $teacher) {
            return;
        }

        // ---- 5. Lessons + topics (one chain per subject; mixed status 0/1 so the
        //        status page shows meaningful complete/incomplete percentages) ----
        $lessonCatalog = [
            'MATH' => ['الأعداد الصحيحة' => ['جمع وطرح الأعداد الصحيحة', 'ضرب وقسمة الأعداد الصحيحة', 'ترتيب العمليات'], 'الكسور العشرية' => ['جمع الكسور العشرية', 'ضرب الكسور العشرية']],
            'SCI' => ['الخلية' => ['مكونات الخلية', 'الخلية النباتية والحيوانية'], 'الطاقة' => ['مصادر الطاقة', 'تحولات الطاقة', 'ترشيد استهلاك الطاقة']],
            'ARB' => ['النحو: الفاعل' => ['تعريف الفاعل', 'إعراب الفاعل'], 'النصوص: الشعر' => ['شرح الأبيات', 'الصور البلاغية']],
            'ENG' => ['Present Simple' => ['Usage and form', 'Negative and questions'], 'Vocabulary: Environment' => ['Key words', 'Reading practice']],
            'ISL' => ['السيرة النبوية' => ['الهجرة النبوية', 'غزوة بدر'], 'الفقه: الصلاة' => ['شروط الصلاة', 'أركان الصلاة']],
            'SOC' => ['الجغرافيا: الخرائط' => ['عناصر الخريطة', 'قراءة الخريطة'], 'التاريخ: الدولة' => ['التأسيس', 'أبرز الأحداث']],
            'PE' => ['اللياقة البدنية' => ['تمارين الإحماء', 'تمارين القوة'], 'كرة القدم' => ['التمرير', 'التسديد']],
            'CS' => ['أساسيات الحاسب' => ['مكونات الحاسب', 'أنظمة التشغيل'], 'البرمجة: سكراتش' => ['اللبنات الأساسية', 'مشروع عملي']],
        ];

        $defaultLessons = [
            'الدرس الأول: مدخل' => ['التمهيد والأهداف', 'التطبيق العملي'],
            'الدرس الثاني: تعميق' => ['شرح المفاهيم', 'تدريبات', 'مراجعة'],
        ];

        $topicIdsBySgs = [];
        foreach ($subjectGroupSubjects as $sgs) {
            $subject = DB::table('subjects')->where('id', $sgs->subject_id)->first();
            $lessons = $lessonCatalog[$subject->code ?? ''] ?? $defaultLessons;

            foreach ($lessons as $lessonName => $topicNames) {
                $lesson = DB::table('lesson')
                    ->where('session_id', $sessionId)
                    ->where('subject_group_subject_id', $sgs->id)
                    ->where('subject_group_class_sections_id', $sgcs->id)
                    ->where('name', $lessonName)
                    ->first();

                $lessonId = $lesson->id ?? DB::table('lesson')->insertGetId([
                    'session_id' => $sessionId,
                    'subject_group_subject_id' => $sgs->id,
                    'subject_group_class_sections_id' => $sgcs->id,
                    'name' => $lessonName,
                ]);

                foreach ($topicNames as $index => $topicName) {
                    // Mix of complete (1) and incomplete (0) so percentages are meaningful.
                    $status = (($index + $lessonId) % 3 === 2) ? 0 : 1;

                    $topic = LessonPlanTopic::updateOrCreate(
                        ['lesson_id' => $lessonId, 'name' => $topicName],
                        [
                            'session_id' => $sessionId,
                            'status' => $status,
                            'complete_date' => $status === 1 ? Carbon::now()->subDays(2)->format('Y-m-d') : null,
                        ]
                    );

                    $topicIdsBySgs[$sgs->id][] = $topic->id;
                }
            }
        }

        // ---- 6. subject_syllabus: one period per day of the current week (+ extras),
        //        so get_weekdates() renders a full 7-day grid for the student's class ----
        $startWeek = strtolower(DB::table('sch_settings')->value('start_week') ?? 'monday');
        $weekStart = Carbon::now()->startOfWeek($startWeek === 'sunday' ? Carbon::SUNDAY : Carbon::MONDAY);

        $timeSlots = [
            ['07:30', '08:15'], ['08:15', '09:00'], ['09:00', '09:45'],
            ['10:00', '10:45'], ['10:45', '11:30'], ['11:30', '12:15'],
        ];

        $sgsList = $subjectGroupSubjects->values();
        $syllabusIds = [];

        for ($day = 0; $day < 7; $day++) {
            // 1-2 periods on school days, none on the last day (tests "not scheduled" cell).
            $periods = $day === 6 ? 0 : ($day % 2 === 0 ? 2 : 1);

            for ($p = 0; $p < $periods; $p++) {
                $sgs = $sgsList[($day + $p) % $sgsList->count()];
                $topicId = $topicIdsBySgs[$sgs->id][($day + $p) % count($topicIdsBySgs[$sgs->id])];
                $slot = $timeSlots[($day + $p) % count($timeSlots)];
                $date = $weekStart->copy()->addDays($day)->format('Y-m-d');

                $existing = DB::table('subject_syllabus')
                    ->where('session_id', $sessionId)
                    ->where('topic_id', $topicId)
                    ->where('date', $date)
                    ->where('time_from', $slot[0])
                    ->where('time_to', $slot[1])
                    ->first();

                if ($existing) {
                    $syllabusIds[] = $existing->id;
                    continue;
                }

                $subject = DB::table('subjects')->where('id', $sgs->subject_id)->first();

                $syllabusIds[] = Syllabus::create([
                    'topic_id' => $topicId,
                    'session_id' => $sessionId,
                    'created_by' => $teacher->id,
                    'created_for' => $teacher->id,
                    'date' => $date,
                    'time_from' => $slot[0],
                    'time_to' => $slot[1],
                    'presentation' => 'عرض تقديمي لدرس ' . ($subject->name ?? ''),
                    'attachment' => '',
                    'lacture_youtube_url' => $day === 1 && $p === 0 ? 'https://www.youtube.com/watch?v=example_lesson' : '',
                    'lacture_video' => '',
                    'sub_topic' => DB::table('topic')->where('id', $topicId)->value('name') ?? '',
                    'teaching_method' => 'الشرح المباشر مع التطبيق العملي',
                    'general_objectives' => 'أن يتقن الطالب مفاهيم الدرس الأساسية',
                    'previous_knowledge' => 'مراجعة الدرس السابق',
                    'comprehensive_questions' => 'أسئلة تقويمية على الدرس',
                    'status' => 1,
                ])->id;
            }
        }

        // ---- 7. lesson_plan_forum comments (student + staff) on the first periods ----
        $studentIds = DB::table('student_session')
            ->where('session_id', $sessionId)
            ->where('class_id', $target->class_id)
            ->where('section_id', $target->section_id)
            ->limit(3)
            ->pluck('student_id');

        $comments = [
            ['type' => 'student', 'message' => 'شكراً أستاذ، هل يمكن توضيح المثال الأخير؟'],
            ['type' => 'staff', 'message' => 'أهلاً بك، سأعيد شرح المثال في الحصة القادمة.'],
            ['type' => 'student', 'message' => 'تم فهم الدرس، شكراً جزيلاً.'],
        ];

        foreach (array_slice($syllabusIds, 0, 3) as $i => $syllabusId) {
            $comment = $comments[$i % count($comments)];

            SyllabusMessage::firstOrCreate(
                [
                    'subject_syllabus_id' => $syllabusId,
                    'type' => $comment['type'],
                    'message' => $comment['message'],
                ],
                [
                    'staff_id' => $comment['type'] === 'staff' ? $teacher->id : null,
                    'student_id' => $comment['type'] === 'student' ? ($studentIds[$i % max($studentIds->count(), 1)] ?? null) : null,
                    'created_date' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
                ]
            );
        }
    }
}
