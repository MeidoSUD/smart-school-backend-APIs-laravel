<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * اختبار دخاني لمسارات القراءة الجديدة للمعلم:
 * academics (classes/sections/subjects)، disable-reasons، online-admissions.
 */
class TeacherAcademicReadTest extends TestCase
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

    public function test_academics_read_endpoints(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/academics/classes', $h)->assertStatus(200)->assertJsonPath('status', 'success');
        $this->getJson('/api/teacher/academics/sections', $h)->assertStatus(200)->assertJsonPath('status', 'success');
        $subjectsRes = $this->getJson('/api/teacher/academics/subjects', $h);
        $subjectsRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'subjects' => [
                        'current_page',
                        'data' => [
                            '*' => ['id', 'name', 'code', 'type'],
                        ],
                        'first_page_url',
                        'from',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);
        $this->assertNotEmpty($subjectsRes->json('data.subjects.data'));

        $pagedRes = $this->getJson('/api/teacher/academics/subjects?per_page=2&page=1', $h);
        $pagedRes->assertStatus(200)
            ->assertJsonPath('data.subjects.per_page', 2)
            ->assertJsonCount(2, 'data.subjects.data');
    }

    public function test_disable_reasons_read_endpoints(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/disable-reasons', $h)->assertStatus(200)->assertJsonPath('status', 'success');
        $this->getJson('/api/teacher/disable-reasons/999999', $h)->assertStatus(404);
    }

    public function test_online_admissions_read_endpoints(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/online-admissions', $h)->assertStatus(200)->assertJsonPath('status', 'success');
        $this->getJson('/api/teacher/online-admissions/999999', $h)->assertStatus(404);
    }

    public function test_academic_read_endpoints_require_auth(): void
    {
        $this->getJson('/api/teacher/academics/classes')->assertStatus(401);
        $this->getJson('/api/teacher/online-admissions')->assertStatus(401);
        $this->getJson('/api/teacher/disable-reasons')->assertStatus(401);
    }
}
