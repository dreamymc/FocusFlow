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
        ->assertJson(['message' => 'Invitation sent successfully.']);

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
