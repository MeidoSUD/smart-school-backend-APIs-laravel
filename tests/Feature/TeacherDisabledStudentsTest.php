<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * اختبار مسار الطلاب المعطلين للمعلم (parity: CI Student::disablestudentslist).
 */
class TeacherDisabledStudentsTest extends TestCase
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

    public function test_disabled_students_requires_auth(): void
    {
        $this->getJson('/api/teacher/students/disabled?class_id=1&section_id=1')->assertStatus(401);
    }

    public function test_disabled_students_validates_required_params(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/students/disabled', $h)->assertStatus(422);
    }

    public function test_disabled_students_out_of_scope_is_forbidden(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/students/disabled?class_id=999999&section_id=999999', $h)
            ->assertStatus(403);
    }

    public function test_disabled_students_in_scope_returns_paginated_list(): void
    {
        $h = $this->teacherHeaders();

        $classes = $this->getJson('/api/teacher/my-classes', $h);
        $classes->assertStatus(200);

        $first = collect($classes->json('data.classes') ?? $classes->json('data') ?? [])
            ->first(fn ($c) => isset($c['class_id'], $c['section_id']));

        if ($first === null) {
            $this->markTestSkipped('Teacher has no class sections in the active session.');
        }

        $res = $this->getJson(
            '/api/teacher/students/disabled?class_id='.$first['class_id'].'&section_id='.$first['section_id'],
            $h
        );

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['students', 'pagination' => ['total', 'per_page', 'current_page', 'last_page']]]);
    }
}
