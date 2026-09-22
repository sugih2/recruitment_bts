<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_username_payload(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'jhon_doe',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'authentication_token',
                'refresh_token',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'jhon_doe',
            'email' => 'jhon_doe@local.test',
        ]);
    }

    public function test_register_rejects_invalid_payload(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'jo',
            'password' => 'short',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure(['message', 'errors']);
    }
}
