<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    public function test_user_can_register_and_login()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(201);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'tester@example.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)->assertJsonFragment(['message' => 'Login successful']);
    }
}
