<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * اختبارات إعدادات القبول الإلكتروني وتقرير الطلاب للمعلم.
 */
class TeacherStudentInfoReportTest extends TestCase
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

    public function test_admission_settings_show(): void
    {
        $h = $this->teacherHeaders();
        $r = $this->getJson('/api/teacher/online-admission/settings', $h);
        $this->assertContains($r->getStatusCode(), [200, 403]);
    }

    public function test_admission_settings_update_validation(): void
    {
        $h = $this->teacherHeaders();
        $this->putJson('/api/teacher/online-admission/settings', [], $h)->assertStatus(422);
        $this->putJson('/api/teacher/online-admission/settings', [
            'online_admission' => 1,
            'online_admission_payment' => 'yes',
            'online_admission_amount' => 0,
        ], $h)->assertStatus(422);
    }

    public function test_student_report_read(): void
    {
        $h = $this->teacherHeaders();
        $r = $this->getJson('/api/teacher/reports/students', $h);
        $this->assertContains($r->getStatusCode(), [200, 403]);
        if ($r->getStatusCode() === 200) {
            $r->assertJsonPath('status', 'success');
        }
    }

    public function test_student_report_requires_auth(): void
    {
        $this->getJson('/api/teacher/reports/students')->assertStatus(401);
        $this->getJson('/api/teacher/online-admission/settings')->assertStatus(401);
    }
}
