<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OnlineExamSeeder extends Seeder
{
    public function run(): void
    {
        $sessionId = DB::table('sch_settings')->value('session_id') ?? DB::table('sessions')->where('is_active', 'yes')->value('id') ?? 28;
        $staffId = DB::table('staff')->value('id') ?? 1;

        $classes = DB::table('classes')->get();
        $classId1 = $classes->first()->id ?? 1;
        $classId2 = $classes->count() > 1 ? $classes->skip(1)->first()->id : $classId1;

        $sections = DB::table('sections')->get();
        $sectionId1 = $sections->first()->id ?? 1;
        $sectionId2 = $sections->count() > 1 ? $sections->skip(1)->first()->id : $sectionId1;

        $classSectionId1 = DB::table('class_sections')->where('class_id', $classId1)->where('section_id', $sectionId1)->value('id') ?? DB::table('class_sections')->value('id');
        $classSectionId2 = DB::table('class_sections')->where('class_id', $classId2)->where('section_id', $sectionId2)->value('id') ?? $classSectionId1;

        $subjectMathId = DB::table('subjects')->where('name', 'like', '%Math%')->orWhere('name', 'like', '%رياضيات%')->value('id') ?? 1;
        $subjectSciId = DB::table('subjects')->where('name', 'like', '%Sci%')->orWhere('name', 'like', '%علوم%')->value('id') ?? 2;
        $subjectArabicId = DB::table('subjects')->where('name', 'like', '%Arab%')->orWhere('name', 'like', '%عربي%')->value('id') ?? 3;
        $subjectEngId = DB::table('subjects')->where('name', 'like', '%Eng%')->orWhere('name', 'like', '%إنجليز%')->value('id') ?? 4;

        Schema::disableForeignKeyConstraints();

        DB::table('onlineexam_student_results')->truncate();
        DB::table('onlineexam_attempts')->truncate();
        DB::table('onlineexam_students')->truncate();
        DB::table('onlineexam_questions')->truncate();
        DB::table('onlineexam')->truncate();
        DB::table('questions')->truncate();

        if (Schema::hasTable('online_exam_results')) {
            DB::table('online_exam_results')->truncate();
        }
        if (Schema::hasTable('online_exam_questions')) {
            DB::table('online_exam_questions')->truncate();
        }
        if (Schema::hasTable('online_exams')) {
            DB::table('online_exams')->truncate();
        }

        Schema::enableForeignKeyConstraints();

        $questionsData = [
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectMathId,
                'question_type' => 'singlechoice',
                'level' => 'easy',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>ما هو ناتج جمع العددين 25 + 75؟</p>',
                'opt_a' => '90',
                'opt_b' => '100',
                'opt_c' => '110',
                'opt_d' => '105',
                'opt_e' => '',
                'correct' => 'opt_b',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectMathId,
                'question_type' => 'singlechoice',
                'level' => 'medium',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>إذا كان س + 5 = 12، فإن قيمة س تساوي:</p>',
                'opt_a' => '5',
                'opt_b' => '6',
                'opt_c' => '7',
                'opt_d' => '8',
                'opt_e' => '',
                'correct' => 'opt_c',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectMathId,
                'question_type' => 'true_false',
                'level' => 'easy',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>مجموع زوايا المثلث الداخلية يساوي 180 درجة.</p>',
                'opt_a' => '',
                'opt_b' => '',
                'opt_c' => '',
                'opt_d' => '',
                'opt_e' => '',
                'correct' => 'true',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectMathId,
                'question_type' => 'multichoice',
                'level' => 'medium',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>أي من الأعداد التالية تعتبر أعداداً أولية؟</p>',
                'opt_a' => '2',
                'opt_b' => '3',
                'opt_c' => '4',
                'opt_d' => '5',
                'opt_e' => '6',
                'correct' => json_encode(['opt_a', 'opt_b', 'opt_d']),
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectMathId,
                'question_type' => 'descriptive',
                'level' => 'hard',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>اشرح باختصار نظرية فيثاغورس وكيفية تطبيقها في إيجاد طول الوتر في المثلث قائم الزاوية.</p>',
                'opt_a' => '',
                'opt_b' => '',
                'opt_c' => '',
                'opt_d' => '',
                'opt_e' => '',
                'correct' => '',
                'descriptive_word_limit' => 150,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectSciId,
                'question_type' => 'singlechoice',
                'level' => 'easy',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>ما هو الرمز الكيميائي للماء؟</p>',
                'opt_a' => 'CO2',
                'opt_b' => 'O2',
                'opt_c' => 'H2O',
                'opt_d' => 'NaCl',
                'opt_e' => '',
                'correct' => 'opt_c',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectSciId,
                'question_type' => 'true_false',
                'level' => 'easy',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>الشمس كوكب يدور حول الأرض.</p>',
                'opt_a' => '',
                'opt_b' => '',
                'opt_c' => '',
                'opt_d' => '',
                'opt_e' => '',
                'correct' => 'false',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectSciId,
                'question_type' => 'singlechoice',
                'level' => 'medium',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>ما هو الكوكب الأقرب إلى الشمس في المجموعة الشمسية؟</p>',
                'opt_a' => 'الزهرة',
                'opt_b' => 'عطارد',
                'opt_c' => 'المريخ',
                'opt_d' => 'المشتري',
                'opt_e' => '',
                'correct' => 'opt_b',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectArabicId,
                'question_type' => 'singlechoice',
                'level' => 'easy',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>ما هو إعراب كلمة "الطالبُ" في جملة: (اجتهدَ الطالبُ)؟</p>',
                'opt_a' => 'مفعول به منصوب',
                'opt_b' => 'فاعل مرفوع وعلامة رفعه الضمة',
                'opt_c' => 'مبتدأ مرفوع',
                'opt_d' => 'خبر مرفوع',
                'opt_e' => '',
                'correct' => 'opt_b',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectArabicId,
                'question_type' => 'true_false',
                'level' => 'easy',
                'class_id' => $classId1,
                'section_id' => $sectionId1,
                'class_section_id' => $classSectionId1,
                'question' => '<p>الفعل المضارع يبدأ دائماً بأحد أحرف كلمة "نأتي".</p>',
                'opt_a' => '',
                'opt_b' => '',
                'opt_c' => '',
                'opt_d' => '',
                'opt_e' => '',
                'correct' => 'true',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectEngId,
                'question_type' => 'singlechoice',
                'level' => 'easy',
                'class_id' => $classId2,
                'section_id' => $sectionId2,
                'class_section_id' => $classSectionId2,
                'question' => '<p>Choose the correct plural form of the word "Child":</p>',
                'opt_a' => 'Childs',
                'opt_b' => 'Children',
                'opt_c' => 'Childrens',
                'opt_d' => 'Childes',
                'opt_e' => '',
                'correct' => 'opt_b',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $staffId,
                'subject_id' => $subjectEngId,
                'question_type' => 'true_false',
                'level' => 'easy',
                'class_id' => $classId2,
                'section_id' => $sectionId2,
                'class_section_id' => $classSectionId2,
                'question' => '<p>The past tense of "Go" is "Went".</p>',
                'opt_a' => '',
                'opt_b' => '',
                'opt_c' => '',
                'opt_d' => '',
                'opt_e' => '',
                'correct' => 'true',
                'descriptive_word_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $insertedQuestionIds = [];
        foreach ($questionsData as $qData) {
            $insertedQuestionIds[] = DB::table('questions')->insertGetId($qData);
        }

        $now = Carbon::now();
        $examsData = [
            [
                'session_id' => $sessionId,
                'exam' => 'اختبار الرياضيات النصفي للفصل الأول',
                'attempt' => 3,
                'exam_from' => $now->copy()->subDays(1)->format('Y-m-d H:i:s'),
                'exam_to' => $now->copy()->addDays(10)->format('Y-m-d H:i:s'),
                'is_quiz' => 0,
                'auto_publish_date' => $now->copy()->addDays(11)->format('Y-m-d H:i:s'),
                'time_from' => '08:00:00',
                'time_to' => '20:00:00',
                'duration' => '01:00:00',
                'passing_percentage' => 50.0,
                'description' => 'اختبار شامل لوحدة الجبر والهندسة يرجى الإجابة عن جميع الأسئلة بدقة.',
                'publish_result' => 0,
                'answer_word_count' => 150,
                'is_active' => '1',
                'is_marks_display' => 1,
                'is_neg_marking' => 0,
                'is_random_question' => 1,
                'is_rank_generated' => 0,
                'publish_exam_notification' => 1,
                'publish_result_notification' => 1,
                'created_at' => now(),
                'updated_at' => now()->toDateString(),
                'questions' => [
                    ['id' => $insertedQuestionIds[0], 'marks' => 5, 'neg' => 0],
                    ['id' => $insertedQuestionIds[1], 'marks' => 5, 'neg' => 0],
                    ['id' => $insertedQuestionIds[2], 'marks' => 5, 'neg' => 0],
                    ['id' => $insertedQuestionIds[3], 'marks' => 5, 'neg' => 0],
                    ['id' => $insertedQuestionIds[4], 'marks' => 10, 'neg' => 0],
                ],
                'target_classes' => [$classId1, $classId2],
                'is_closed' => false,
            ],
            [
                'session_id' => $sessionId,
                'exam' => 'كويز العلوم السريع - مادة الكيمياء والفيزياء',
                'attempt' => 2,
                'exam_from' => $now->copy()->subHours(5)->format('Y-m-d H:i:s'),
                'exam_to' => $now->copy()->addDays(7)->format('Y-m-d H:i:s'),
                'is_quiz' => 1,
                'auto_publish_date' => $now->copy()->addDays(8)->format('Y-m-d H:i:s'),
                'time_from' => '09:00:00',
                'time_to' => '22:00:00',
                'duration' => '00:30:00',
                'passing_percentage' => 60.0,
                'description' => 'اختبار قصير يقيس مهارات الاستيعاب العلمي والمفاهيم الكيميائية الأساسية.',
                'publish_result' => 0,
                'answer_word_count' => 0,
                'is_active' => '1',
                'is_marks_display' => 1,
                'is_neg_marking' => 0,
                'is_random_question' => 0,
                'is_rank_generated' => 0,
                'publish_exam_notification' => 1,
                'publish_result_notification' => 1,
                'created_at' => now(),
                'updated_at' => now()->toDateString(),
                'questions' => [
                    ['id' => $insertedQuestionIds[5], 'marks' => 10, 'neg' => 0],
                    ['id' => $insertedQuestionIds[6], 'marks' => 5, 'neg' => 0],
                    ['id' => $insertedQuestionIds[7], 'marks' => 10, 'neg' => 0],
                ],
                'target_classes' => [$classId1],
                'is_closed' => false,
            ],
            [
                'session_id' => $sessionId,
                'exam' => 'اختبار اللغة العربية وقواعد النحو',
                'attempt' => 1,
                'exam_from' => $now->copy()->subDays(2)->format('Y-m-d H:i:s'),
                'exam_to' => $now->copy()->addDays(14)->format('Y-m-d H:i:s'),
                'is_quiz' => 0,
                'auto_publish_date' => $now->copy()->addDays(15)->format('Y-m-d H:i:s'),
                'time_from' => '07:00:00',
                'time_to' => '23:00:00',
                'duration' => '00:45:00',
                'passing_percentage' => 50.0,
                'description' => 'اختبار في النحو والصرف وتطبيقات القراءة والفهم القرائي.',
                'publish_result' => 0,
                'answer_word_count' => 100,
                'is_active' => '1',
                'is_marks_display' => 1,
                'is_neg_marking' => 0,
                'is_random_question' => 1,
                'is_rank_generated' => 0,
                'publish_exam_notification' => 1,
                'publish_result_notification' => 1,
                'created_at' => now(),
                'updated_at' => now()->toDateString(),
                'questions' => [
                    ['id' => $insertedQuestionIds[8], 'marks' => 10, 'neg' => 0],
                    ['id' => $insertedQuestionIds[9], 'marks' => 10, 'neg' => 0],
                ],
                'target_classes' => [$classId1, $classId2],
                'is_closed' => false,
            ],
            [
                'session_id' => $sessionId,
                'exam' => 'English Language Diagnostic Assessment (Closed)',
                'attempt' => 1,
                'exam_from' => $now->copy()->subDays(15)->format('Y-m-d H:i:s'),
                'exam_to' => $now->copy()->subDays(5)->format('Y-m-d H:i:s'),
                'is_quiz' => 1,
                'auto_publish_date' => $now->copy()->subDays(4)->format('Y-m-d H:i:s'),
                'time_from' => '08:00:00',
                'time_to' => '18:00:00',
                'duration' => '00:40:00',
                'passing_percentage' => 50.0,
                'description' => 'تقييم تشخيصي لتحديد مستوى الطلاب في مفردات وقواعد اللغة الإنجليزية.',
                'publish_result' => 1,
                'answer_word_count' => 0,
                'is_active' => '1',
                'is_marks_display' => 1,
                'is_neg_marking' => 0,
                'is_random_question' => 0,
                'is_rank_generated' => 1,
                'publish_exam_notification' => 1,
                'publish_result_notification' => 1,
                'created_at' => now()->subDays(16),
                'updated_at' => now()->subDays(4)->toDateString(),
                'questions' => [
                    ['id' => $insertedQuestionIds[10], 'marks' => 10, 'neg' => 0],
                    ['id' => $insertedQuestionIds[11], 'marks' => 10, 'neg' => 0],
                ],
                'target_classes' => [$classId1, $classId2],
                'is_closed' => true,
            ],
        ];

        $studentSessions = DB::table('student_session')->where('is_active', 'yes')->get();

        foreach ($examsData as $exam) {
            $questions = $exam['questions'];
            $targetClasses = $exam['target_classes'];
            $isClosed = $exam['is_closed'];

            unset($exam['questions']);
            unset($exam['target_classes']);
            unset($exam['is_closed']);

            $onlineExamId = DB::table('onlineexam')->insertGetId($exam);

            $examQuestionMap = [];
            foreach ($questions as $q) {
                $eqId = DB::table('onlineexam_questions')->insertGetId([
                    'question_id' => $q['id'],
                    'onlineexam_id' => $onlineExamId,
                    'session_id' => $sessionId,
                    'marks' => $q['marks'],
                    'neg_marks' => $q['neg'],
                    'is_active' => '1',
                    'created_at' => now(),
                    'updated_at' => now()->toDateString(),
                ]);
                $examQuestionMap[$q['id']] = [
                    'onlineexam_question_id' => $eqId,
                    'marks' => $q['marks'],
                ];
            }

            foreach ($studentSessions as $ss) {
                if (!in_array($ss->class_id, $targetClasses) && count($targetClasses) > 0) {
                    continue;
                }

                $isAttempted = $isClosed ? 1 : 0;
                $onlineExamStudentId = DB::table('onlineexam_students')->insertGetId([
                    'onlineexam_id' => $onlineExamId,
                    'student_session_id' => $ss->id,
                    'is_attempted' => $isAttempted,
                    'rank' => $isClosed ? rand(1, 10) : 0,
                    'quiz_attempted' => $isAttempted,
                    'created_at' => now(),
                    'updated_at' => now()->toDateString(),
                ]);

                if ($isClosed) {
                    DB::table('onlineexam_attempts')->insert([
                        'onlineexam_student_id' => $onlineExamStudentId,
                        'created_at' => Carbon::now()->subDays(6),
                        'updated_at' => Carbon::now()->subDays(6)->toDateString(),
                    ]);

                    foreach ($questions as $q) {
                        $qRecord = DB::table('questions')->where('id', $q['id'])->first();
                        $selectedOption = $qRecord ? $qRecord->correct : 'opt_b';
                        $eqId = $examQuestionMap[$q['id']]['onlineexam_question_id'];
                        $obtainedMark = $examQuestionMap[$q['id']]['marks'];

                        DB::table('onlineexam_student_results')->insert([
                            'onlineexam_student_id' => $onlineExamStudentId,
                            'onlineexam_question_id' => $eqId,
                            'select_option' => $selectedOption,
                            'marks' => $obtainedMark,
                            'remark' => 'إجابة صحيحة وممتازة',
                            'attachment_name' => '',
                            'attachment_upload_name' => '',
                            'created_at' => Carbon::now()->subDays(6),
                            'updated_at' => Carbon::now()->subDays(6)->toDateString(),
                        ]);
                    }
                }
            }

            if (Schema::hasTable('online_exams')) {
                $laravelExamId = DB::table('online_exams')->insertGetId([
                    'exam_title' => $exam['exam'],
                    'exam_type' => $exam['is_quiz'] ? 'quiz' : 'term',
                    'class_id' => $targetClasses[0] ?? $classId1,
                    'section_id' => $sectionId1,
                    'subject_id' => $questions[0]['id'] ?? 1,
                    'duration' => $exam['duration'],
                    'minimum_percentage' => $exam['passing_percentage'],
                    'max_attempts' => $exam['attempt'],
                    'is_active' => $exam['is_active'] == '1' ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (Schema::hasTable('online_exam_questions')) {
                    foreach ($questions as $q) {
                        DB::table('online_exam_questions')->insert([
                            'online_exam_id' => $laravelExamId,
                            'question_id' => $q['id'],
                            'optional' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
