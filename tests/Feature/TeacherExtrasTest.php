<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * اختبار دخاني لنقاط المعلم المكتملة الجديدة (100% plan).
 */
class TeacherExtrasTest extends TestCase
{
    private function teacherHeaders(): array
    {
        $login = $this->postJson('/api/teacher/auth/login', [
            'identifier' => 't',
            'password' => '22222222',
        ]);
        $login->assertStatus(200);

        return ['Authorization' => 'Bearer '.$login->json('data.token')];
    }

    public function test_teacher_extras_smoke(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/profile', $h)->assertStatus(200)->assertJsonPath('status', 'success');
        $this->getJson('/api/teacher/my-leaves/types', $h)->assertStatus(200);
        $this->getJson('/api/teacher/my-leaves', $h)->assertStatus(200);
        $this->getJson('/api/teacher/my-leaves/balance', $h)->assertStatus(200);
        $this->getJson('/api/teacher/chat/conversations', $h)->assertStatus(200);
        $this->getJson('/api/teacher/chat/search-user', $h)->assertStatus(200);
        $this->getJson('/api/teacher/calendar/events', $h)->assertStatus(200);
        $this->getJson('/api/teacher/calendar/todos', $h)->assertStatus(200);
        $this->getJson('/api/teacher/complaints', $h)->assertStatus(200);
        $this->getJson('/api/teacher/homework-closed', $h)->assertStatus(200);
        $this->getJson('/api/teacher/daily-assignments', $h)->assertStatus(200);
        $this->getJson('/api/teacher/exam-groups', $h)->assertStatus(200);
        $this->getJson('/api/teacher/exam-schedules', $h)->assertStatus(200);
        $ms = $this->getJson('/api/teacher/exam-marksheet?exam_group_id=1&class_id=1&section_id=1', $h);
        $this->assertTrue(in_array($ms->getStatusCode(), [200, 403], true));
        $this->getJson('/api/teacher/online-exams', $h)->assertStatus(200);
        $ma = $this->getJson('/api/teacher/attendance/month?student_id=1&month=1&year=2026', $h);
        $this->assertTrue(in_array($ma->getStatusCode(), [200, 403, 404], true));
        $this->getJson('/api/teacher/contents/999999', $h)->assertStatus(404);
        $this->getJson('/api/teacher/complaints/999999', $h)->assertStatus(404);
        $this->getJson('/api/teacher/grades', $h)->assertStatus(200);
        $this->getJson('/api/teacher/student-categories', $h)->assertStatus(200);
        $this->getJson('/api/teacher/student-houses', $h)->assertStatus(200);
        $this->getJson('/api/teacher/questions/999999', $h)->assertStatus(404);
    }

    public function test_teacher_calendar_todo_flow(): void
    {
        $h = $this->teacherHeaders();
        $create = $this->postJson('/api/teacher/calendar/todos', [
            'event_title' => 'Smoke todo',
            'start_date' => date('Y-m-d'),
        ], $h);
        $create->assertStatus(200);
        $id = $create->json('data.id');
        $this->assertNotEmpty($id);
        $this->postJson("/api/teacher/calendar/todos/{$id}/complete", [], $h)->assertStatus(200);
    }

    public function test_teacher_complaint_validation(): void
    {
        $h = $this->teacherHeaders();
        $this->postJson('/api/teacher/complaints', [], $h)->assertStatus(422);
    }
}
