<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * P1–P5 موديول المعلم: طلابي، تحضير، واجبات، منهج، درجات، امتحانات، جدول، إجازات، تواصل، محتوى.
 * البيانات ديناميكية من نطاق المعلم t/22222222 — مع تنظيف الصفوف المنشأة.
 */
class TeacherPhasesTest extends TestCase
{
    private string $token;
    private array $h;
    private array $class;

    protected function setUp(): void
    {
        parent::setUp();
        $login = $this->postJson('/api/teacher/auth/login', [
            'identifier' => 't', 'password' => '22222222',
        ]);
        $login->assertStatus(200);
        $this->token = $login->json('data.token');
        $this->h = ['Authorization' => "Bearer {$this->token}"];

        $classes = $this->getJson('/api/teacher/my-classes', $this->h)->json('data.classes');
        $this->assertNotEmpty($classes, 'Teacher must have at least one class');
        $this->class = $classes[0];
    }

    protected function tearDown(): void
    {
        if (isset($this->token)) {
            $this->postJson('/api/teacher/auth/logout', [], $this->h);
        }
        parent::tearDown();
    }

    public function test_p1_students_scoped(): void
    {
        // بدون باراميترات → 422
        $this->getJson('/api/teacher/students', $this->h)->assertStatus(422);

        $r = $this->getJson(
            "/api/teacher/students?class_id={$this->class['class_id']}&section_id={$this->class['section_id']}",
            $this->h
        );
        $r->assertStatus(200)->assertJsonStructure(['data' => ['students', 'pagination']]);

        // خارج النطاق → 403
        $this->getJson('/api/teacher/students?class_id=999999&section_id=999999', $this->h)
            ->assertStatus(403);

        $first = $r->json('data.students.0');
        if ($first) {
            $this->getJson("/api/teacher/students/{$first['id']}", $this->h)
                ->assertStatus(200)->assertJsonPath('data.student.id', $first['id']);
        }
        $this->getJson('/api/teacher/students/99999999', $this->h)->assertStatus(404);
    }

    public function test_p2_attendance_flow(): void
    {
        $base = "class_id={$this->class['class_id']}&section_id={$this->class['section_id']}";
        $date = date('Y-m-d');

        $this->getJson("/api/teacher/attendance?$base", $this->h)->assertStatus(422);

        $list = $this->getJson("/api/teacher/attendance?$base&date=$date", $this->h);
        $list->assertStatus(200)->assertJsonStructure(['data' => ['students', 'attendence_types']]);

        $first = $list->json('data.students.0');
        $this->assertNotNull($first, 'Need a student to save attendance');

        $save = $this->postJson('/api/teacher/attendance', [
            'class_id' => $this->class['class_id'], 'section_id' => $this->class['section_id'],
            'date' => $date,
            'records' => [[
                'student_session_id' => $first['student_session_id'],
                'attendence_type_id' => 1, 'remark' => 'test',
            ]],
        ], $this->h);
        $save->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertEquals(1, $save->json('data.inserted') + $save->json('data.updated'));

        // إعادة الحفظ = تحديث لا إدخال مكرر
        $save2 = $this->postJson('/api/teacher/attendance', [
            'class_id' => $this->class['class_id'], 'section_id' => $this->class['section_id'],
            'date' => $date,
            'records' => [[
                'student_session_id' => $first['student_session_id'],
                'attendence_type_id' => 2, 'remark' => 'test2',
            ]],
        ], $this->h);
        $save2->assertStatus(200);
        $this->assertEquals(1, $save2->json('data.updated'));

        $this->getJson("/api/teacher/attendance/report?$base&from=$date&to=$date", $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['report']]);

        // تنظيف
        DB::table('student_attendences')
            ->where('student_session_id', $first['student_session_id'])
            ->whereDate('date', $date)->delete();

        // خارج النطاق → 403
        $this->postJson('/api/teacher/attendance', [
            'class_id' => 999999, 'section_id' => 999999, 'date' => $date,
            'records' => [['student_session_id' => 1, 'attendence_type_id' => 1]],
        ], $this->h)->assertStatus(403);
    }

    public function test_p3_homework_flow(): void
    {
        // مادة من مواد المعلم
        $subs = $this->getJson(
            "/api/teacher/my-subjects?class_id={$this->class['class_id']}&section_id={$this->class['section_id']}",
            $this->h
        );
        $subs->assertStatus(200);
        $sgsList = DB::table('teacher_subjects')
            ->join('subject_group_subjects as sgs', 'sgs.subject_id', '=', 'teacher_subjects.subject_id')
            ->where('teacher_id', 1)->limit(1)->value('sgs.id');
        $this->assertNotNull($sgsList, 'Need a subject_group_subject for homework test');

        $create = $this->postJson('/api/teacher/homework', [
            'class_id' => $this->class['class_id'], 'section_id' => $this->class['section_id'],
            'subject_group_subject_id' => $sgsList,
            'homework_date' => date('Y-m-d'), 'submit_date' => date('Y-m-d', strtotime('+2 days')),
            'description' => 'p3 test homework',
        ], $this->h);
        $create->assertStatus(200);
        $hwId = $create->json('data.id');

        $this->getJson('/api/teacher/homework?mine=1', $this->h)
            ->assertStatus(200)->assertJsonFragment(['description' => 'p3 test homework']);

        $this->putJson("/api/teacher/homework/$hwId", ['description' => 'p3 updated'], $this->h)
            ->assertStatus(200);

        $eval = $this->getJson("/api/teacher/homework/$hwId/evaluation", $this->h);
        $eval->assertStatus(200)->assertJsonStructure(['data' => ['homework', 'students']]);

        $first = $eval->json('data.students.0');
        if ($first) {
            $this->postJson('/api/teacher/homework/evaluate', [
                'homework_id' => $hwId,
                'records' => [[
                    'student_session_id' => $first['student_session_id'],
                    'marks' => 8, 'note' => 'good',
                ]],
            ], $this->h)->assertStatus(200);
        }

        $this->deleteJson("/api/teacher/homework/$hwId", [], $this->h)->assertStatus(200);
        DB::table('homework_evaluation')->where('homework_id', $hwId)->delete();
    }

    public function test_p3_syllabus_flow(): void
    {
        $this->getJson('/api/teacher/lessons', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['lessons']]);

        $status = $this->getJson('/api/teacher/syllabus/status', $this->h);
        $status->assertStatus(200)
            ->assertJsonStructure(['data' => ['total', 'complete', 'incomplete', 'complete_percent']]);

        $this->getJson('/api/teacher/syllabus', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['syllabus']]);
    }

    public function test_p4_marks_exams(): void
    {
        $this->getJson('/api/teacher/exams', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['exam_schedules']]);

        $this->getJson(
            "/api/teacher/marks/sheet?exam_id=1&class_id={$this->class['class_id']}&section_id={$this->class['section_id']}",
            $this->h
        )->assertStatus(200)->assertJsonStructure(['data' => ['exam_schedules', 'students', 'results_available']]);

        // حفظ الدرجات يتطلب جدول exam_results (غير موجود في قاعدة التطوير) → 503 موثق
        $saveBlocked = $this->postJson('/api/teacher/marks', [
            'records' => [['exam_schedule_id' => 1, 'student_id' => 1, 'get_marks' => 10]],
        ], $this->h);
        $this->assertContains($saveBlocked->getStatusCode(), [200, 503]);

        $this->getJson('/api/teacher/online-exams', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['online_exams']]);

        $q = $this->getJson('/api/teacher/questions', $this->h);
        $q->assertStatus(200)->assertJsonStructure(['data' => ['questions']]);

        // سؤال تجريبي + تنظيف
        $csId = $this->class['class_section_id'];
        $subjectId = DB::table('subjects')->value('id');
        $create = $this->postJson('/api/teacher/questions', [
            'subject_id' => $subjectId, 'class_section_id' => $csId,
            'question' => 'p4 test question',
        ], $this->h);
        $create->assertStatus(200);
        DB::table('questions')->where('id', $create->json('data.id'))->delete();

        // خارج النطاق → 403
        $this->postJson('/api/teacher/questions', [
            'subject_id' => $subjectId, 'class_section_id' => 999999,
            'question' => 'x',
        ], $this->h)->assertStatus(403);
    }

    public function test_p5_timetable_leaves_communicate_content(): void
    {
        $this->getJson('/api/teacher/timetable', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['timetable']]);

        $this->getJson(
            "/api/teacher/timetable/class?class_id={$this->class['class_id']}&section_id={$this->class['section_id']}",
            $this->h
        )->assertStatus(200);

        $this->getJson('/api/teacher/timetable/class?class_id=999999&section_id=999999', $this->h)
            ->assertStatus(403);

        $this->getJson('/api/teacher/leaves', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['leaves']]);

        $this->getJson('/api/teacher/notice-board', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['notifications']]);

        $this->getJson('/api/teacher/staff-directory', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['staff']]);

        $this->postJson('/api/teacher/notices', ['title' => 'x'], $this->h)
            ->assertStatus(422);

        $sent = $this->postJson('/api/teacher/notices', [
            'title' => 'p5 test', 'message' => 'hello class',
        ], $this->h);
        $sent->assertStatus(200);
        $this->getJson('/api/teacher/notices/sent', $this->h)
            ->assertStatus(200)->assertJsonFragment(['title' => 'p5 test']);
        DB::table('send_notification')->where('id', $sent->json('data.id'))->delete();

        $this->getJson('/api/teacher/contents', $this->h)
            ->assertStatus(200)->assertJsonStructure(['data' => ['contents']]);

        $share = $this->postJson('/api/teacher/contents', [
            'title' => 'p5 test content',
            'class_section_ids' => [$this->class['class_section_id']],
        ], $this->h);
        $share->assertStatus(200);
        $shareId = $share->json('data.id');
        DB::table('share_content_for')->where('share_content_id', $shareId)->delete();
        DB::table('share_contents')->where('id', $shareId)->delete();

        // نطاق المحتوى → 403
        $this->postJson('/api/teacher/contents', [
            'title' => 'x', 'class_section_ids' => [999999],
        ], $this->h)->assertStatus(403);
    }
}
