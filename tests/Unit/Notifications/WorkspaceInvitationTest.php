<?php

use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\WorkspaceInvitation;
use App\Enums\WorkspaceRole;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sends notification via mail, database, and broadcast channels for registered users', function () {
    Notification::fake();

    $workspace = Workspace::factory()->create(['name' => 'Test Workspace']);
    $inviter = User::factory()->create(['name' => 'Alice']);
    $invitee = User::factory()->create(['email' => 'invited@example.com']);

    $invitation = Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'invited@example.com',
        'role' => WorkspaceRole::Member,
    ]);

    $invitee->notify(new WorkspaceInvitation($invitation, $inviter->name));

    Notification::assertSentTo(
        $invitee,
        WorkspaceInvitation::class,
        function ($notification, $channels) {
            return in_array('mail', $channels)
                && in_array('database', $channels)
                && in_array('broadcast', $channels);
        }
    );
});

it('sends only mail for on-demand (unregistered) notifiables', function () {
    $workspace = Workspace::factory()->create(['name' => 'Design Team']);
    $inviter = User::factory()->create(['name' => 'Bob']);

    $invitation = Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'unregistered@example.com',
        'role' => WorkspaceRole::Viewer,
    ]);

    $notification = new WorkspaceInvitation($invitation, $inviter->name);

    // Simulate on-demand notification — notifiable is an AnonymousNotifiable, not a User
    $notifiable = new \Illuminate\Notifications\AnonymousNotifiable();
    $notifiable->route('mail', 'unregistered@example.com');
    $channels = $notification->via($notifiable);

    expect($channels)->toBe(['mail']);
});

it('contains correct invitation data in notification', function () {
    $workspace = Workspace::factory()->create(['name' => 'Design Team']);
    $inviter = User::factory()->create(['name' => 'Bob']);
    $invitee = User::factory()->create();

    $invitation = Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => $invitee->email,
        'role' => WorkspaceRole::Viewer,
        'token' => 'secret-token',
    ]);

    $notification = new WorkspaceInvitation($invitation, $inviter->name);

    // Test mail representation
    $mail = $notification->toMail($invitee);
    expect($mail->subject)->toContain('Design Team');
    expect($mail->introLines[0])->toContain('Bob');
    expect($mail->introLines[0])->toContain('Design Team');
    expect($mail->actionText)->toBe('Accept Invitation');

    // Test database representation
    $data = $notification->toArray($invitee);
    expect($data['workspace_name'])->toBe('Design Team');
    expect($data['inviter_name'])->toBe('Bob');
    expect($data['role'])->toBe('viewer');
    expect($data)->not->toHaveKey('token');
});
