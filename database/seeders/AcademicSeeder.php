<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Classe;
use App\Models\Section;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\SubjectGroup;
use App\Models\SubjectGroupSubject;
use App\Models\SubjectGroupClassSection;
use App\Models\Exam;
use App\Models\ExamGroup;
use App\Models\ExamGroupClassBatchExam;
use App\Models\ExamGroupClassBatchExamSubject;
use App\Models\ExamGroupClassBatchExamStudent;
use App\Models\ExamSchedule;
use App\Models\Grade;
use App\Models\MarksDivision;
use App\Models\Homework;
use App\Models\HomeworkEvaluation;
use App\Models\LessonPlan;
use App\Models\LessonPlanTopic;
use App\Models\Syllabus;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamResult;
use App\Models\StudentSession;
use App\Models\TeacherSubject;
use App\Models\ClassTimetable;
use App\Models\DailyAssignment;
use App\Models\SubmitAssignment;
use App\Models\Session;
use App\Models\Staff;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        if (Exam::exists()) {
            return;
        }

        $session = Session::first();
        if (!$session) {
            return;
        }

        $teacher = Staff::where('employee_id', 'TCH2024001')->first();

        // ===== Classes & Sections (skip if already exist) =====
        $classes = [];
        if (!Classe::exists()) {
            $classNames = ['الصف الأول', 'الصف الثاني', 'الصف الثالث', 'الصف الرابع', 'الصف الخامس', 'الصف السادس'];
            foreach ($classNames as $name) {
                $classes[] = Classe::create(['class' => $name, 'is_active' => 'yes']);
            }
        } else {
            $classes = Classe::whereIn('class', ['الصف الأول', 'الصف الثاني', 'الصف الثالث', 'الصف الرابع', 'الصف الخامس', 'الصف السادس'])->get()->all();
            if (empty($classes)) {
                $classes = Classe::all()->all();
            }
        }

        $sectionA = Section::firstOrCreate(['section' => 'A'], ['is_active' => 'yes']);
        $sectionB = Section::firstOrCreate(['section' => 'B'], ['is_active' => 'yes']);

        $classSections = ClassSection::exists() ? ClassSection::all()->all() : [];
        if (empty($classSections)) {
            foreach ($classes as $class) {
                foreach ([$sectionA, $sectionB] as $sec) {
                    $classSections[] = ClassSection::create([
                        'class_id' => $class->id,
                        'section_id' => $sec->id,
                        'is_active' => 'yes',
                    ]);
                }
            }
        }

        // ===== Subjects (skip if already exist) =====
        $subjectModels = [];
        if (!Subject::exists()) {
            $subjectNames = [
                ['name' => 'الرياضيات', 'code' => 'MATH', 'type' => 'theory'],
                ['name' => 'العلوم', 'code' => 'SCI', 'type' => 'theory'],
                ['name' => 'اللغة العربية', 'code' => 'ARB', 'type' => 'theory'],
                ['name' => 'اللغة الإنجليزية', 'code' => 'ENG', 'type' => 'theory'],
                ['name' => 'التربية الإسلامية', 'code' => 'ISL', 'type' => 'theory'],
                ['name' => 'الدراسات الاجتماعية', 'code' => 'SOC', 'type' => 'theory'],
                ['name' => 'التربية البدنية', 'code' => 'PE', 'type' => 'practical'],
                ['name' => 'الحاسب الآلي', 'code' => 'CS', 'type' => 'practical'],
            ];

            foreach ($subjectNames as $s) {
                $subjectModels[] = Subject::create([
                    'name' => $s['name'],
                    'code' => $s['code'],
                    'type' => $s['type'],
                    'is_active' => 'yes',
                ]);
            }
        } else {
            $subjectModels = Subject::all()->all();
        }

        // ===== Subject Groups (skip if already exist) =====
        $subjectGroup = SubjectGroup::firstOrCreate(
            ['name' => 'المواد الأساسية'],
            ['description' => 'المجموعة الأساسية للمواد الدراسية', 'session_id' => $session->id]
        );

        if (!SubjectGroupSubject::where('subject_group_id', $subjectGroup->id)->exists()) {
            foreach ($subjectModels as $subject) {
                SubjectGroupSubject::create([
                    'subject_group_id' => $subjectGroup->id,
                    'session_id' => $session->id,
                    'subject_id' => $subject->id,
                ]);
            }
        }

        if (!SubjectGroupClassSection::where('subject_group_id', $subjectGroup->id)->exists()) {
            foreach ($classSections as $cs) {
                SubjectGroupClassSection::create([
                    'subject_group_id' => $subjectGroup->id,
                    'class_section_id' => $cs->id,
                    'session_id' => $session->id,
                    'description' => '',
                    'is_active' => 1,
                ]);
            }
        }

        // ===== Teacher Subjects =====
        if ($teacher && !TeacherSubject::exists()) {
            foreach ($subjectModels as $subject) {
                TeacherSubject::create([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'class_section_id' => $classSections[0]->id,
                    'session_id' => $session->id,
                ]);
            }
        }

        // ===== Class Timetable (skip if already exist) =====
        if (!ClassTimetable::exists()) {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Sunday'];
            $times = [
                ['from' => '07:30', 'to' => '08:15'],
                ['from' => '08:15', 'to' => '09:00'],
                ['from' => '09:15', 'to' => '10:00'],
                ['from' => '10:00', 'to' => '10:45'],
                ['from' => '11:00', 'to' => '11:45'],
                ['from' => '11:45', 'to' => '12:30'],
            ];

            foreach ($classSections as $classSection) {
                foreach ($days as $dayIndex => $day) {
                    foreach ($times as $timeIndex => $time) {
                        $subjectIndex = ($dayIndex * count($times) + $timeIndex) % count($subjectModels);
                        ClassTimetable::create([
                            'session_id' => $session->id,
                            'class_id' => $classSection->class_id,
                            'section_id' => $classSection->section_id,
                            'subject_group_id' => $subjectGroup->id,
                            'subject_group_subject_id' => SubjectGroupSubject::where('subject_group_id', $subjectGroup->id)
                                ->where('subject_id', $subjectModels[$subjectIndex]->id)->first()->id ?? null,
                            'staff_id' => $teacher ? $teacher->id : null,
                            'day' => $day,
                            'time_from' => $time['from'],
                            'time_to' => $time['to'],
                            'room_no' => 'G-' . str_pad($timeIndex + 1, 2, '0', STR_PAD_LEFT),
                        ]);
                    }
                }
            }
        }

        // ===== Exams =====
        $exam = Exam::create([
            'name' => 'الامتحان النهائي',
            'sesion_id' => $session->id,
            'note' => 'الامتحان النهائي للفصل الدراسي',
            'is_active' => 'yes',
        ]);

        $examGroup = ExamGroup::create([
            'name' => 'امتحانات الفصل الأول',
            'exam_type' => 'final',
            'description' => 'امتحانات نهاية الفصل الدراسي الأول',
            'is_active' => 1,
        ]);

        $batchExam = ExamGroupClassBatchExam::create([
            'exam' => 'امتحان الرياضيات',
            'passing_percentage' => 50.00,
            'session_id' => $session->id,
            'date_from' => now()->addMonth()->format('Y-m-d'),
            'date_to' => now()->addMonth()->addDays(3)->format('Y-m-d'),
            'exam_group_id' => $examGroup->id,
            'is_publish' => 0,
            'is_active' => 1,
        ]);

        foreach ($subjectModels as $index => $subject) {
            ExamGroupClassBatchExamSubject::create([
                'exam_group_class_batch_exams_id' => $batchExam->id,
                'subject_id' => $subject->id,
                'date_from' => now()->addMonth()->addDays($index)->format('Y-m-d'),
                'time_from' => '09:00:00',
                'duration' => '02:00',
                'room_no' => 'H-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'max_marks' => 100.00,
                'min_marks' => 20.00,
                'credit_hours' => 2.00,
                'is_active' => 1,
            ]);
        }

        $studentSessions = StudentSession::all();
        foreach ($studentSessions as $ss) {
            ExamGroupClassBatchExamStudent::create([
                'exam_group_class_batch_exam_id' => $batchExam->id,
                'student_id' => $ss->student_id,
                'student_session_id' => $ss->id,
                'roll_no' => $ss->student?->roll_no,
                'is_active' => 1,
            ]);
        }

        // ===== Exam Schedules =====
        foreach ($subjectModels as $index => $subject) {
            ExamSchedule::create([
                'session_id' => $session->id,
                'exam_id' => $exam->id,
                'class_id' => $classSections[0]->class_id,
                'date_of_exam' => now()->addMonth()->addDays($index)->format('Y-m-d'),
                'start_to' => '09:00',
                'end_from' => '11:00',
                'room_no' => 'H-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'full_marks' => 100,
                'passing_marks' => 20,
                'note' => '',
                'is_active' => 'yes',
            ]);
        }

        // ===== Grades =====
        if (!Grade::exists()) {
            $gradeData = [
                ['exam_type' => 'final', 'name' => 'A+', 'point' => 4.0, 'mark_from' => 90, 'mark_upto' => 100, 'description' => 'ممتاز'],
                ['exam_type' => 'final', 'name' => 'A', 'point' => 3.7, 'mark_from' => 80, 'mark_upto' => 89.99, 'description' => 'جيد جداً'],
                ['exam_type' => 'final', 'name' => 'B+', 'point' => 3.3, 'mark_from' => 70, 'mark_upto' => 79.99, 'description' => 'جيد'],
                ['exam_type' => 'final', 'name' => 'B', 'point' => 3.0, 'mark_from' => 60, 'mark_upto' => 69.99, 'description' => 'مقبول'],
                ['exam_type' => 'final', 'name' => 'C', 'point' => 2.0, 'mark_from' => 50, 'mark_upto' => 59.99, 'description' => 'ضعيف'],
                ['exam_type' => 'final', 'name' => 'F', 'point' => 1.0, 'mark_from' => 0, 'mark_upto' => 49.99, 'description' => 'راسب'],
            ];
            foreach ($gradeData as $g) {
                Grade::create($g);
            }
        }

        // ===== Marks Division =====
        if (!MarksDivision::exists()) {
            MarksDivision::create([
                'name' => 'امتياز',
                'percentage_from' => 90,
                'percentage_to' => 100,
                'is_active' => 1,
            ]);
            MarksDivision::create([
                'name' => 'جيد جداً',
                'percentage_from' => 75,
                'percentage_to' => 89.99,
                'is_active' => 1,
            ]);
            MarksDivision::create([
                'name' => 'جيد',
                'percentage_from' => 60,
                'percentage_to' => 74.99,
                'is_active' => 1,
            ]);
        }

        // ===== Homework =====
        if ($teacher && $studentSessions->isNotEmpty()) {
            foreach ($studentSessions as $ss) {
                $homework = Homework::create([
                    'class_id' => $ss->class_id,
                    'section_id' => $ss->section_id,
                    'session_id' => $session->id,
                    'staff_id' => $teacher->id,
                    'subject_id' => $subjectModels[0]->id,
                    'homework_date' => now()->format('Y-m-d'),
                    'submission_date' => now()->addDays(3)->format('Y-m-d'),
                    'submit_date' => now()->addDays(3)->format('Y-m-d'),
                    'marks' => 20.00,
                    'description' => 'حل التمارين من الصفحة 15 إلى 20',
                    'create_date' => now()->format('Y-m-d'),
                    'created_by' => $teacher->id,
                    'document' => '',
                ]);

                HomeworkEvaluation::create([
                    'homework_id' => $homework->id,
                    'student_id' => $ss->student_id,
                    'student_session_id' => $ss->id,
                    'marks' => 18.00,
                    'note' => 'عمل ممتاز',
                    'date' => now()->addDays(3)->format('Y-m-d'),
                    'status' => 'completed',
                ]);
            }

            // Daily Assignment
            foreach ($studentSessions as $ss) {
                DailyAssignment::create([
                    'student_session_id' => $ss->id,
                    'subject_group_subject_id' => SubjectGroupSubject::first()->id,
                    'title' => 'تدريبات نحوية',
                    'description' => 'إعراب الجمل التالية',
                    'date' => now()->format('Y-m-d'),
                    'evaluated_by' => $teacher->id,
                    'evaluation_date' => now()->addDays(2)->format('Y-m-d'),
                    'remark' => 'مستوى جيد',
                ]);
            }
        }

        // ===== Lesson Plan =====
        $lesson = null;
        if ($teacher) {
            $lessonId = DB::table('lesson')->insertGetId([
                'session_id' => $session->id,
                'subject_group_subject_id' => SubjectGroupSubject::first()->id,
                'subject_group_class_sections_id' => SubjectGroupClassSection::first()->id,
                'name' => 'الأعداد الصحيحة',
            ]);

            $topic = LessonPlanTopic::create([
                'session_id' => $session->id,
                'lesson_id' => $lessonId,
                'name' => 'جمع وطرح الأعداد الصحيحة',
                'status' => 1,
                'complete_date' => now()->addWeek()->format('Y-m-d'),
            ]);

            Syllabus::create([
                'topic_id' => $topic->id,
                'session_id' => $session->id,
                'created_by' => $teacher->id,
                'created_for' => $teacher->id,
                'date' => now()->format('Y-m-d'),
                'time_from' => '09:00',
                'time_to' => '09:45',
                'presentation' => '',
                'attachment' => '',
                'lacture_youtube_url' => '',
                'lacture_video' => '',
                'sub_topic' => 'جمع الأعداد الصحيحة',
                'teaching_method' => 'الشرح المباشر',
                'general_objectives' => 'فهم عملية جمع الأعداد الصحيحة',
                'previous_knowledge' => 'الأعداد الطبيعية',
                'comprehensive_questions' => 'ما هو ناتج جمع -5 + 3؟',
                'status' => 1,
            ]);
        }

        // ===== Online Exams =====
        if (!DB::table('onlineexam')->exists()) {
            DB::table('onlineexam')->insert([
                'session_id' => $session->id,
                'exam' => 'اختبار الرياضيات الإلكتروني',
                'attempt' => 2,
                'exam_from' => now()->format('Y-m-d H:i:s'),
                'exam_to' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'duration' => '01:00:00',
                'passing_percentage' => 50,
                'description' => 'اختبار إلكتروني في مادة الرياضيات',
                'is_active' => '1',
                'is_quiz' => 0,
                'is_marks_display' => 1,
                'is_neg_marking' => 0,
                'is_random_question' => 0,
                'publish_exam_notification' => 0,
                'publish_result_notification' => 0,
            ]);
        }

        // ===== Content Types =====
        if (!DB::table('content_types')->exists()) {
            DB::table('content_types')->insert([
                'name' => 'ملفات تعليمية',
                'description' => 'محتوى تعليمي للفصول الدراسية',
                'is_active' => 1,
                'created_at' => now(),
            ]);
        }

        if (!DB::table('contents')->exists()) {
            DB::table('contents')->insert([
                'title' => 'ملخص قواعد اللغة العربية',
                'type' => 'pdf',
                'is_public' => 'Yes',
                'class_id' => $classSections[0]->class_id,
                'cls_sec_id' => $classSections[0]->id,
                'file' => '',
                'date' => now()->format('Y-m-d'),
                'note' => 'ملخص شامل لقواعد اللغة العربية للفصل الأول',
                'is_active' => 'yes',
                'created_by' => $teacher?->id ?? 1,
                'created_at' => now(),
            ]);
        }
    }
}
