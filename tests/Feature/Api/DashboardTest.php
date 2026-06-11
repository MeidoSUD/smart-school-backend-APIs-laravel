<?php

namespace Tests\Feature\Api;

class DashboardTest extends ApiTestCase
{
    public function test_dashboard_returns_attendance_percentage(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson('/api/user/dashboard');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'attendence_percentage',
                    'homeworklist',
                    'notificationlist',
                    'student_data',
                ],
            ]);

        $this->assertNotSame(-1.0, $response->json('data.attendence_percentage'));
    }
}
