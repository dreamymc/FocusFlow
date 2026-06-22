<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\Invitation;
use App\Enums\WorkspaceRole;
use App\Enums\InviteStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows pending invitations page', function () {
    $user = User::factory()->create(['email' => 'dev@example.com']);
    $workspace = Workspace::factory()->create(['name' => 'Alpha']);

    Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'dev@example.com',
        'role' => WorkspaceRole::Member,
        'status' => InviteStatus::Pending,
    ]);

    $response = $this->actingAs($user)->get('/invitations');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Invitations/Index')
            ->has('invitations', 1)
            ->where('invitations.0.workspace_name', 'Alpha')
        );
});

it('accepts invitation via the web flow', function () {
    $user = User::factory()->create(['email' => 'accept@example.com']);
    $workspace = Workspace::factory()->create();

    $invitation = Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'accept@example.com',
        'role' => WorkspaceRole::Member,
        'status' => InviteStatus::Pending,
    ]);

    $response = $this->actingAs($user)
        ->post('/invitations/accept', ['token' => $invitation->token]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('workspace_user', [
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
    ]);
});

it('declines invitation via the web flow', function () {
    $user = User::factory()->create(['email' => 'decline@example.com']);
    $workspace = Workspace::factory()->create();

    $invitation = Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'decline@example.com',
        'role' => WorkspaceRole::Member,
        'status' => InviteStatus::Pending,
    ]);

    $response = $this->actingAs($user)
        ->delete("/invitations/{$invitation->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('invitations', [
        'id' => $invitation->id,
        'status' => InviteStatus::Declined->value,
    ]);
});

it('prevents declining another user\'s invitation', function () {
    $user = User::factory()->create(['email' => 'correct@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);
    $workspace = Workspace::factory()->create();

    $invitation = Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'other@example.com',
        'role' => WorkspaceRole::Member,
        'status' => InviteStatus::Pending,
    ]);

    $response = $this->actingAs($user)
        ->delete("/invitations/{$invitation->id}");

    $response->assertStatus(403);
});

it('requires auth to view invitations page', function () {
    $response = $this->get('/invitations');
    $response->assertRedirect('/login');
});

it('requires auth to accept invitation', function () {
    $response = $this->post('/invitations/accept', ['token' => 'some-token']);
    $response->assertRedirect('/login');
});

it('requires auth to decline invitation', function () {
    $invitation = Invitation::factory()->create();
    $response = $this->delete("/invitations/{$invitation->id}");
    $response->assertRedirect('/login');
});
