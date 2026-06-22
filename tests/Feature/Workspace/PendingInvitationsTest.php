<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\Invitation;
use App\Enums\WorkspaceRole;
use App\Enums\InviteStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists pending invitations for authenticated user', function () {
    $user = User::factory()->create(['email' => 'invited@example.com']);
    $workspace = Workspace::factory()->create(['name' => 'My Workspace']);

    Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'invited@example.com',
        'role' => WorkspaceRole::Member,
        'status' => InviteStatus::Pending,
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/v1/invitations/pending');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.workspace_name', 'My Workspace')
        ->assertJsonPath('data.0.role', 'member')
        ->assertJsonMissingPath('data.0.token');
});

it('excludes accepted/declined invitations', function () {
    $user = User::factory()->create(['email' => 'invited@example.com']);
    $workspace = Workspace::factory()->create();

    // Non-pending statuses
    Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'invited@example.com',
        'status' => InviteStatus::Accepted,
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/v1/invitations/pending');

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});

it('requires authentication', function () {
    $response = $this->getJson('/api/v1/invitations/pending');
    $response->assertUnauthorized();
});
