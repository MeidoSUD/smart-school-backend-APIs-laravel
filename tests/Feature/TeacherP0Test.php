<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * P0 موديول المعلم: الدخول + النطاق + اللوحة (TeacherScope).
 * يستخدم بيانات التطوير الحالية (t/22222222 معلم، s/22222222 طالب).
 */
class TeacherP0Test extends TestCase
{
    private function teacherToken(): string
    {
        $login = $this->postJson('/api/teacher/auth/login', [
            'identifier' => 't',
            'password' => '22222222',
        ]);
        $login->assertStatus(200)->assertJsonPath('status', 'success');

        return $login->json('data.token');
    }

    public function test_teacher_login_rejects_wrong_password(): void
    {
        $this->postJson('/api/teacher/auth/login', [
            'identifier' => 't',
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    public function test_teacher_login_validation(): void
    {
        $this->postJson('/api/teacher/auth/login', [])
            ->assertStatus(422);
    }

    public function test_teacher_me_requires_auth(): void
    {
        $this->getJson('/api/teacher/me')->assertStatus(401);
    }

    public function test_student_cannot_use_teacher_endpoints(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'username' => 's',
            'password' => '22222222',
        ]);
        $login->assertStatus(200);
        $token = $login->json('data.token');

        $this->getJson('/api/teacher/me', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(403);
    }

    public function test_teacher_p0_flow(): void
    {
        $token = $this->teacherToken();
        $h = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/teacher/me', $h)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['user', 'staff', 'roles']]);

        $dash = $this->getJson('/api/teacher/dashboard', $h);
        $dash->assertStatus(200)->assertJsonPath('status', 'success');
        $dash->assertJsonStructure(['data' => [
            'role_id', 'session_id', 'staff', 'my_classes_count',
            'my_subjects_count', 'my_students_count', 'unread_notifications', 'my_classes',
        ]]);

        $this->getJson('/api/teacher/my-classes', $h)
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['classes']]);

        $classes = $this->getJson('/api/teacher/my-classes', $h)->json('data.classes');
        if (! empty($classes)) {
            $this->getJson(
                '/api/teacher/my-subjects?class_id=' . $classes[0]['class_id']
                . '&section_id=' . $classes[0]['section_id'],
                $h
            )->assertStatus(200)->assertJsonStructure(['data' => ['subjects']]);
        }

        // خارج النطاق → 403
        $this->getJson('/api/teacher/my-subjects?class_id=999999&section_id=999999', $h)
            ->assertStatus(403);

        $this->postJson('/api/teacher/auth/logout', [], $h)->assertStatus(200);
    }

    public function test_existing_student_teacher_route_kept_numeric(): void
    {
        // ثابتة /me يجب ألا يبتلعها wildcard — وغير المسجل → 401 لا 404.
        $this->getJson('/api/teacher/me')->assertStatus(401);

        $token = $this->teacherToken();
        // مسار الطالب القديم ما زال رقمياً فقط: نص غير رقمي → 404، رقمي → ليس 404.
        $this->getJson('/api/teacher/abc', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(404);

        $this->postJson('/api/teacher/auth/logout', [], ['Authorization' => "Bearer {$token}"]);
    }
}
