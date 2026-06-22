<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Actions\InviteMemberAction;
use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use App\Enums\InviteStatus;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Support\Facades\Notification;

it('invites a member and sends notification to registered users', function () {
    Notification::fake();

    $workspace = Workspace::factory()->create();
    $inviter = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $action = app(InviteMemberAction::class);
    $invitation = $action->execute($workspace, 'invitee@example.com', WorkspaceRole::Member, $inviter);

    expect($invitation->email)->toBe('invitee@example.com')
        ->and($invitation->role->value)->toBe(WorkspaceRole::Member->value)
        ->and($invitation->status->value)->toBe(InviteStatus::Pending->value);

    Notification::assertSentTo($invitee, WorkspaceInvitation::class);
});

it('sends mail-only notification to unregistered emails', function () {
    Notification::fake();

    $workspace = Workspace::factory()->create();
    $inviter = User::factory()->create();

    $action = app(InviteMemberAction::class);
    $action->execute($workspace, 'unregistered@example.com', WorkspaceRole::Member, $inviter);

    Notification::assertSentOnDemand(WorkspaceInvitation::class, function ($notification, $channels, $notifiable) {
        return $notifiable->routes['mail'] === 'unregistered@example.com';
    });
});

it('does not crash when re-inviting to same workspace+email after decline', function () {
    $workspace = Workspace::factory()->create();
    $inviter = User::factory()->create();

    $action = app(InviteMemberAction::class);

    // First invite — creates a record
    $first = $action->execute($workspace, 'test@example.com', WorkspaceRole::Member, $inviter);

    // Manually mark as declined (simulating decline flow)
    $first->update(['status' => InviteStatus::Declined]);

    // Second invite — must NOT throw unique constraint violation
    $second = $action->execute($workspace, 'test@example.com', WorkspaceRole::Member, $inviter);

    expect($second->id)->not->toBe($first->id)
        ->and($second->status->value)->toBe(InviteStatus::Pending->value);
});
