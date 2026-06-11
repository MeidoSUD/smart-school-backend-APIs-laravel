<?php

namespace Tests\Feature\Api;

class TeacherListTest extends ApiTestCase
{
    public function test_teacher_list_returns_staff_for_class_section(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson('/api/teacher');

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertNotEmpty($response->json('data.teacherlist'));
    }
}
