<?php

namespace App\Notifications;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invitation $invitation,
        public string $inviterName
    ) {
        $this->invitation->load('workspace');
    }

    public function via(object $notifiable): array
    {
        // On-demand notifications (bare email string) get mail only
        // Registered User models get mail + database + broadcast
        return $notifiable instanceof User
            ? ['mail', 'database', 'broadcast']
            : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $workspaceName = optional($this->invitation->workspace)->name ?? 'a workspace';
        $acceptUrl = route('invitations.index', ['token' => $this->invitation->token]);

        return (new MailMessage)
            ->subject("You've been invited to {$workspaceName}")
            ->greeting("Hello!")
            ->line("{$this->inviterName} has invited you to join **{$workspaceName}** as a **{$this->invitation->role->value}**.")
            ->action('Accept Invitation', $acceptUrl)
            ->line('If you did not expect this invitation, you can ignore this email.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'workspace_id' => $this->invitation->workspace_id,
            'workspace_name' => optional($this->invitation->workspace)->name ?? 'a workspace',
            'inviter_name' => $this->inviterName,
            'role' => $this->invitation->role->value,
        ];
    }
}
