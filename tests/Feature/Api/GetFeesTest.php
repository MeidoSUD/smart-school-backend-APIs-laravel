<?php

namespace Tests\Feature\Api;

class GetFeesTest extends ApiTestCase
{
    public function test_getfees_returns_fee_arrays(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson('/api/user/getfees');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => [
                    'student_due_fee',
                    'transport_fees',
                    'student_discount_fee',
                ],
            ]);
    }
}
