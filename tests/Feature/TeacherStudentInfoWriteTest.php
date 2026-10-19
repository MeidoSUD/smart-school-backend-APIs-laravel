<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * اختبارات كتابة قائمة Student Information للمعلم:
 * أسباب التعطيل (add/edit/delete) والقبول الإلكتروني (delete).
 */
class TeacherStudentInfoWriteTest extends TestCase
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

    public function test_disable_reason_write_validation(): void
    {
        $h = $this->teacherHeaders();

        $this->postJson('/api/teacher/disable-reasons', [], $h)->assertStatus(422);
        $this->putJson('/api/teacher/disable-reasons/999999', [], $h)->assertStatus(422);
    }

    public function test_disable_reason_crud_roundtrip(): void
    {
        $h = $this->teacherHeaders();

        $create = $this->postJson('/api/teacher/disable-reasons', ['name' => 'Smoke reason'], $h);
        $this->assertContains($create->getStatusCode(), [200, 403], 'create must be 200 (allowed) or 403 (RBAC)');

        if ($create->getStatusCode() === 200) {
            $id = $create->json('data.id');
            $this->putJson("/api/teacher/disable-reasons/{$id}", ['name' => 'Smoke reason 2'], $h)->assertStatus(200);
            $this->deleteJson("/api/teacher/disable-reasons/{$id}", [], $h)->assertStatus(200);
            $this->deleteJson("/api/teacher/disable-reasons/{$id}", [], $h)->assertStatus(404);
        }
    }

    public function test_online_admission_delete_missing_returns_404_or_403(): void
    {
        $h = $this->teacherHeaders();

        $r = $this->deleteJson('/api/teacher/online-admissions/999999', [], $h);
        $this->assertContains($r->getStatusCode(), [403, 404], 'missing row must not succeed');
    }

    public function test_write_endpoints_require_auth(): void
    {
        $this->postJson('/api/teacher/disable-reasons', ['name' => 'x'])->assertStatus(401);
        $this->deleteJson('/api/teacher/online-admissions/1')->assertStatus(401);
    }
}
