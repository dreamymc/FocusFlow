<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('accepts an invite successfully', function () {
    $user = User::factory()->create(['email' => 'invited@example.com']);
    $workspace = Workspace::factory()->create();
    
    $token = Str::random(32);
    DB::table('invitations')->insert([
        'workspace_id' => $workspace->id,
        'email' => 'invited@example.com',
        'role' => 'member',
        'token' => $token,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/invitations/accept', [
            'token' => $token,
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Joined workspace successfully.']);

    $this->assertDatabaseHas('workspace_user', [
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => 'member',
    ]);
});

it('rejects invalid invite tokens', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/invitations/accept', [
            'token' => 'invalid-token',
        ]);

    $response->assertStatus(404);
});

it('requires authenticated user to have matching email', function () {
    $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);
    $workspace = Workspace::factory()->create();
    
    $token = Str::random(32);
    DB::table('invitations')->insert([
        'workspace_id' => $workspace->id,
        'email' => 'invited@example.com',
        'role' => 'member',
        'token' => $token,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($wrongUser)
        ->postJson('/api/v1/invitations/accept', [
            'token' => $token,
        ]);

    $response->assertStatus(403);
});
