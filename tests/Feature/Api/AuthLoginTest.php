<?php

namespace Tests\Feature\Api;

class AuthLoginTest extends ApiTestCase
{
    public function test_login_returns_token_and_user_payload(): void
    {
        $fixtures = $this->seedSchoolFixtures();

        $response = $this->postJson('/api/auth/login', [
            'username' => $fixtures['username'],
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_rejects_invalid_password(): void
    {
        $fixtures = $this->seedSchoolFixtures();

        $response = $this->postJson('/api/auth/login', [
            'username' => $fixtures['username'],
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }
}
