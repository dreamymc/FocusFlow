<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs out a user successfully', function () {
    $user = User::factory()->create();
    
    // In sanctum, a user logs in and gets a token
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/logout');

    $response->assertOk()
        ->assertJson(['message' => 'Logged out successfully']);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('prevents unauthenticated user from logging out', function () {
    $response = $this->postJson('/api/v1/logout');

    $response->assertUnauthorized();
});
