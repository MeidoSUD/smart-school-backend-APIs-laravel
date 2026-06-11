<?php

namespace Tests\Feature\Api;

class AttendanceRangeTest extends ApiTestCase
{
    public function test_attendance_range_returns_calendar_events(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson('/api/attendence/getAttendence?start=' . now()->startOfMonth()->toDateString() . '&end=' . now()->endOfMonth()->toDateString());

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertIsArray($response->json('data'));
    }
}
