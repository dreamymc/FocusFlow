<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('invites a member to a workspace successfully', function () {
    $admin = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($admin, ['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/workspaces/{$workspace->id}/invite", [
            'email' => 'new-member@example.com',
            'role' => 'member',
        ]);

    $response->assertCreated()
        ->assertJson([
            'message' => 'Invitation sent successfully.',
            'data' => [
                'email' => 'new-member@example.com',
                'role' => 'member',
            ]
        ])
        ->assertJsonMissingPath('data.token');

    $this->assertDatabaseHas('invitations', [
        'workspace_id' => $workspace->id,
        'email' => 'new-member@example.com',
        'role' => 'member',
    ]);
});

it('prevents non-admins from inviting members', function () {
    $member = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($member, ['role' => 'member']);

    $response = $this->actingAs($member)
        ->postJson("/api/v1/workspaces/{$workspace->id}/invite", [
            'email' => 'another@example.com',
            'role' => 'member',
        ]);

    $response->assertForbidden();
});

it('rejects unauthenticated invite requests', function () {
    $workspace = Workspace::factory()->create();

    $response = $this->postJson("/api/v1/workspaces/{$workspace->id}/invite", [
        'email' => 'test@example.com',
        'role' => 'member',
    ]);

    $response->assertUnauthorized();
});

it('validates invite data', function () {
    $admin = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->users()->attach($admin, ['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/workspaces/{$workspace->id}/invite", [
            'email' => 'not-an-email',
            'role' => 'invalid-role',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'role']);
});

it('accepts invitation by id via api', function () {
    $user = User::factory()->create(['email' => 'api-accept@example.com']);
    $workspace = Workspace::factory()->create();

    $invitation = \App\Models\Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'api-accept@example.com',
        'role' => \App\Enums\WorkspaceRole::Member,
        'status' => \App\Enums\InviteStatus::Pending,
    ]);

    $response = $this->actingAs($user)
        ->postJson("/api/v1/invitations/{$invitation->id}/accept");

    $response->assertOk()
        ->assertJson(['message' => 'Joined workspace successfully.']);

    $this->assertDatabaseHas('workspace_user', [
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);
});

it('declines invitation by id via api', function () {
    $user = User::factory()->create(['email' => 'api-decline@example.com']);
    $workspace = Workspace::factory()->create();

    $invitation = \App\Models\Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'api-decline@example.com',
        'role' => \App\Enums\WorkspaceRole::Member,
        'status' => \App\Enums\InviteStatus::Pending,
    ]);

    $response = $this->actingAs($user)
        ->postJson("/api/v1/invitations/{$invitation->id}/decline");

    $response->assertOk()
        ->assertJson(['message' => 'Invitation declined.']);

    $this->assertDatabaseHas('invitations', [
        'id' => $invitation->id,
        'status' => \App\Enums\InviteStatus::Declined->value,
    ]);
});

it('prevents acting on other users invitations', function () {
    $user = User::factory()->create(['email' => 'sneaky@example.com']);
    $workspace = Workspace::factory()->create();

    $invitation = \App\Models\Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'victim@example.com',
        'role' => \App\Enums\WorkspaceRole::Member,
        'status' => \App\Enums\InviteStatus::Pending,
    ]);

    $this->actingAs($user)
        ->postJson("/api/v1/invitations/{$invitation->id}/accept")
        ->assertForbidden();

    $this->actingAs($user)
        ->postJson("/api/v1/invitations/{$invitation->id}/decline")
        ->assertForbidden();
});
