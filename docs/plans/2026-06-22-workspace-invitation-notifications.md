# Workspace Invitation Notifications & Acceptance UI

> **For Claude:** REQUIRED SUB-SKILL: Use executing-plans to implement this plan task-by-task.

**Goal:** Deliver email + in-app notification when a workspace invitation is sent, and provide a web UI for the invited user to view and accept pending invitations.

**Architecture:** Extend `InviteMemberAction` to dispatch a Laravel Notification (mail + database + broadcast) via the notification system's built-in broadcasting on `App.Models.User.{id}`. No custom broadcast event needed — Laravel's notification system auto-broadcasts via the `broadcast` channel. Add a new web controller + Inertia page for listing/accepting invitations. The `NotificationBell` listens to `.notification()` on the user's Echo channel.

**Tech Stack:** Laravel Notifications (mail, database, broadcast channels), Laravel Echo + Reverb, Inertia + Vue 3 + shadcn/vue, Pest tests.

**Key decisions (architecture skill → trade-off analysis):**
- ✅ **Notifications over raw event** — one class gives us mail + database + broadcast. Less code than 3 separate concerns.
- ✅ **On-demand notifications for unregistered emails** — route mail to bare email if no User record exists. `via()` gating prevents database/broadcast channels from firing on non-User notifiables.
- ✅ **`.notification()` listener over custom event** — Laravel's `broadcast` channel on Notifications already pushes to `App.Models.User.{id}`. A custom `InvitationCreated` event is redundant. Saves one event class, one test, and keeps all invitation logic in `WorkspaceInvitation.php`.
- ✅ **Delete all statuses on re-invite** — the `unique(workspace_id, email)` constraint crashes if a `Declined` record remains. Delete without status filter.
- ✅ **Full page over modal** — invitations exist outside workspace scope. A page at `/invitations` is accessible without a current workspace.
- ✅ **`intended()` redirect** — preserve `?token=` query params through registration flow.

---

## Task 1: Run notifications table migration

**Files:**
- Run: `php artisan notifications:table && php artisan migrate`

### Step 1: Create and run the migration

```bash
php artisan notifications:table
php artisan migrate
```

### Step 2: Verify

```bash
php artisan migrate:status | grep notifications
```
Expected: `Ran` status shown.

### Step 3: Commit

```bash
git add database/migrations/*create_notifications_table.php
git commit -m "chore: add notifications table for database notifications"
```

---

## Task 2: Create `InvitationFactory`

**Files:**
- Create: `database/factories/InvitationFactory.php`

The plan's tests reference `Invitation::factory()`. The model uses `HasFactory` but no factory exists.

### Step 1: Create the factory

**File:** `database/factories/InvitationFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Workspace;
use App\Enums\InviteStatus;
use App\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'email' => fake()->safeEmail(),
            'role' => WorkspaceRole::Member->value,
            'token' => Str::random(40),
            'status' => InviteStatus::Pending->value,
        ];
    }
}
```

### Step 2: Create a quick smoke test

Add to an existing test file or run a quick Pest assertion:

```bash
php artisan tinker --execute="(new \Database\Factories\InvitationFactory())->definition();"
```

Expected: array with all fields populated.

### Step 3: Commit

```bash
git add database/factories/InvitationFactory.php
git commit -m "feat: add InvitationFactory for tests"
```

---

## Task 3: Create `WorkspaceInvitation` Notification

**Files:**
- Create: `app/Notifications/WorkspaceInvitation.php`
- Test: `tests/Unit/Notifications/WorkspaceInvitationTest.php`

### Step 1: Write the test

**File:** `tests/Unit/Notifications/WorkspaceInvitationTest.php`

```php
<?php

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

    $invitation = \App\Models\Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'invited@example.com',
        'role' => WorkspaceRole::Member,
    ]);

    $invitee->notify(new WorkspaceInvitation($invitation, $inviter));

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

    $invitation = \App\Models\Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => 'unregistered@example.com',
        'role' => WorkspaceRole::Viewer,
    ]);

    $notification = new WorkspaceInvitation($invitation, $inviter);

    // Simulate on-demand notification — notifiable is a string email, not a User
    $channels = $notification->via('unregistered@example.com');

    expect($channels)->toBe(['mail']);
});

it('contains correct invitation data in notification', function () {
    $workspace = Workspace::factory()->create(['name' => 'Design Team']);
    $inviter = User::factory()->create(['name' => 'Bob']);
    $invitee = User::factory()->create();

    $invitation = \App\Models\Invitation::factory()->create([
        'workspace_id' => $workspace->id,
        'email' => $invitee->email,
        'role' => WorkspaceRole::Viewer,
        'token' => 'secret-token',
    ]);

    $notification = new WorkspaceInvitation($invitation, $inviter);

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
    expect($data['token'])->toBe('secret-token');
});
```

Run: `php artisan test --filter=WorkspaceInvitationTest`
Expected: FAIL — class not found.

### Step 2: Create the Notification class

**File:** `app/Notifications/WorkspaceInvitation.php`

```php
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
        public User $inviter
    ) {}

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
        $acceptUrl = url('/invitations?token=' . $this->invitation->token);

        return (new MailMessage)
            ->subject("You've been invited to {$this->invitation->workspace->name}")
            ->greeting("Hello!")
            ->line("{$this->inviter->name} has invited you to join **{$this->invitation->workspace->name}** as a **{$this->invitation->role->value}**.")
            ->action('Accept Invitation', $acceptUrl)
            ->line('If you did not expect this invitation, you can ignore this email.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'workspace_id' => $this->invitation->workspace->id,
            'workspace_name' => $this->invitation->workspace->name,
            'inviter_name' => $this->inviter->name,
            'role' => $this->invitation->role->value,
            'token' => $this->invitation->token,
        ];
    }
}
```

### Step 3: Run test to verify it passes

Run: `php artisan test --filter=WorkspaceInvitationTest`
Expected: PASS

### Step 4: Commit

```bash
git add app/Notifications/WorkspaceInvitation.php tests/Unit/Notifications/WorkspaceInvitationTest.php
git commit -m "feat: add WorkspaceInvitation notification (mail + database + broadcast)"
```

---

## Task 4: Update `InviteMemberAction` — fix unique constraint + add on-demand notifications

**Files:**
- Modify: `app/Actions/InviteMemberAction.php`
- Modify: `app/Http/Controllers/Api/V1/InvitationController.php`
- Modify: `app/Http/Controllers/Web/WorkspaceController.php`
- Test: `tests/Unit/Actions/InviteMemberActionTest.php`
- Test: `tests/Feature/Workspace/InviteMemberTest.php`

### Step 1: Write failing tests

**File:** `tests/Unit/Actions/InviteMemberActionTest.php`

Replace existing content:

```php
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

    Notification::assertSentViaMail(WorkspaceInvitation::class, 'unregistered@example.com');
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
```

Run: `php artisan test --filter=InviteMemberActionTest`
Expected: FAIL — `execute()` missing 4th argument, etc.

### Step 2: Update `InviteMemberAction`

**File:** `app/Actions/InviteMemberAction.php`

```php
<?php

namespace App\Actions;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use App\Enums\InviteStatus;
use App\Notifications\WorkspaceInvitation;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteMemberAction
{
    public function execute(Workspace $workspace, string $email, WorkspaceRole $role, User $inviter): Invitation
    {
        // Delete ANY existing invitation for this workspace+email
        // (not just Pending — Declined records would violate unique constraint)
        Invitation::where('workspace_id', $workspace->id)
            ->where('email', $email)
            ->delete();

        $invitation = Invitation::create([
            'workspace_id' => $workspace->id,
            'email' => $email,
            'role' => $role->value,
            'token' => Str::random(40),
            'status' => InviteStatus::Pending->value,
        ]);

        // Send notification
        $invitedUser = User::where('email', $email)->first();

        if ($invitedUser) {
            // Registered user → mail + database + broadcast
            $invitedUser->notify(new WorkspaceInvitation($invitation, $inviter));
        } else {
            // Unregistered email → mail-only via on-demand notification
            Notification::route('mail', $email)
                ->notify(new WorkspaceInvitation($invitation, $inviter));
        }

        return $invitation;
    }
}
```

### Step 3: Update call sites

**File:** `app/Http/Controllers/Api/V1/InvitationController.php` — change `invite()` method:

```php
public function invite(StoreInvitationRequest $request, Workspace $workspace, InviteMemberAction $inviteMemberAction): JsonResponse
{
    $invitation = $inviteMemberAction->execute(
        $workspace,
        $request->validated('email'),
        WorkspaceRole::from($request->validated('role')),
        $request->user()
    );

    return response()->json([
        'message' => 'Invitation sent successfully.',
        'data' => [
            'email' => $invitation->email,
            'role' => $invitation->role->value,
        ]
    ], 201);
}
```

Note: `token` removed from API response (security — should only go via email link).

**File:** `app/Http/Controllers/Web/WorkspaceController.php` — change `invite()` method:

```php
public function invite(Request $request, Workspace $workspace, InviteMemberAction $inviteMemberAction)
{
    if (!$request->user()->hasRole(WorkspaceRole::Admin->value)) {
        abort(403, 'Only workspace admins can invite members.');
    }

    $validated = $request->validate([
        'email' => ['required', 'email'],
        'role' => ['required', 'string', 'in:member,viewer'],
    ]);

    $inviteMemberAction->execute(
        $workspace,
        $validated['email'],
        WorkspaceRole::from($validated['role']),
        $request->user()
    );

    return redirect()->route('workspaces.settings', $workspace)
        ->with('success', "Invitation sent to {$validated['email']}.");
}
```

### Step 4: Update feature test

**File:** `tests/Feature/Workspace/InviteMemberTest.php`

Update `it('invites a member to a workspace successfully')`:

```php
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
```

### Step 5: Run all tests to verify

```bash
php artisan test --filter=InviteMember
php artisan test --filter=InviteMemberActionTest
```

Expected: All PASS

### Step 6: Commit

```bash
git add app/Actions/InviteMemberAction.php app/Http/Controllers/Api/V1/InvitationController.php app/Http/Controllers/Web/WorkspaceController.php tests/Unit/Actions/InviteMemberActionTest.php tests/Feature/Workspace/InviteMemberTest.php
git commit -m "feat: fix unique constraint bug, add on-demand notifications for unregistered emails"
```

---

## Task 5: Create API endpoint for pending invitations

**Files:**
- Create: `app/Http/Resources/InvitationResource.php`
- Modify: `routes/api.php`
- Modify: `app/Http/Controllers/Api/V1/InvitationController.php`
- Test: `tests/Feature/Workspace/PendingInvitationsTest.php`

### Step 1: Write the test

**File:** `tests/Feature/Workspace/PendingInvitationsTest.php`

```php
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
```

Run: `php artisan test --filter=PendingInvitationsTest`
Expected: FAIL — 404 (route not found)

### Step 2: Create API Resource

**File:** `app/Http/Resources/InvitationResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'workspace_name' => $this->workspace->name,
            'role' => $this->role->value,
            'created_at' => $this->created_at,
        ];
    }
}
```

Token is intentionally excluded.

### Step 3: Add controller method + route

**File:** `app/Http/Controllers/Api/V1/InvitationController.php`

Add method:

```php
use App\Http\Resources\InvitationResource;
use App\Enums\InviteStatus;

public function pending(Request $request): JsonResponse
{
    $invitations = \App\Models\Invitation::where('email', $request->user()->email)
        ->where('status', InviteStatus::Pending)
        ->with('workspace')
        ->get();

    return response()->json([
        'data' => InvitationResource::collection($invitations),
    ]);
}
```

**File:** `routes/api.php`

Add inside `auth:sanctum` group, after the accept route, before workspace-scoped routes:

```php
Route::get('invitations/pending', [InvitationController::class, 'pending'])->name('invitations.pending');
```

### Step 4: Run test to verify it passes

Run: `php artisan test --filter=PendingInvitationsTest`
Expected: PASS

### Step 5: Commit

```bash
git add app/Http/Resources/InvitationResource.php app/Http/Controllers/Api/V1/InvitationController.php routes/api.php tests/Feature/Workspace/PendingInvitationsTest.php
git commit -m "feat: add API endpoint for pending invitations"
```

---

## Task 6: Create Invitations web page for accepting invitations

**Files:**
- Create: `app/Http/Controllers/Web/InvitationWebController.php`
- Create: `resources/js/Pages/Invitations/Index.vue`
- Modify: `routes/web.php`
- Test: `tests/Feature/Workspace/InvitationWebControllerTest.php`

### Step 1: Write the test

**File:** `tests/Feature/Workspace/InvitationWebControllerTest.php`

```php
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

it('requires auth to view invitations page', function () {
    $response = $this->get('/invitations');
    $response->assertRedirect('/login');
});
```

Run: `php artisan test --filter=InvitationWebControllerTest`
Expected: FAIL — 404s

### Step 2: Create the web controller

**File:** `app/Http/Controllers/Web/InvitationWebController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Actions\AcceptInviteAction;
use App\Enums\InviteStatus;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationWebController extends Controller
{
    public function index(Request $request)
    {
        $invitations = Invitation::where('email', $request->user()->email)
            ->where('status', InviteStatus::Pending)
            ->with('workspace')
            ->get();

        return Inertia::render('Invitations/Index', [
            'invitations' => $invitations->map(fn ($inv) => [
                'id' => $inv->id,
                'workspace_name' => $inv->workspace->name,
                'role' => $inv->role->value,
                'token' => $inv->token,
                'created_at' => $inv->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function accept(Request $request, AcceptInviteAction $acceptInviteAction)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $workspace = $acceptInviteAction->execute($validated['token'], $request->user());
            session(['current_workspace_id' => $workspace->id]);
            return redirect()->route('dashboard')
                ->with('success', "You've joined {$workspace->name}!");
        } catch (\Exception $e) {
            return redirect()->route('invitations.index')
                ->with('error', 'Invalid or expired invitation token.');
        }
    }

    public function decline(Request $request, Invitation $invitation)
    {
        if ($invitation->email !== $request->user()->email) {
            abort(403);
        }

        $invitation->update(['status' => InviteStatus::Declined]);

        return redirect()->route('invitations.index')
            ->with('success', 'Invitation declined.');
    }
}
```

### Step 3: Create the Vue page

**File:** `resources/js/Pages/Invitations/Index.vue`

```vue
<script setup>
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  invitations: {
    type: Array,
    default: () => [],
  },
});

const accept = (token) => {
  router.post('/invitations/accept', { token }, {
    onSuccess: () => {
      toast.success('Invitation accepted!');
    },
    onError: () => {
      toast.error('Failed to accept invitation. It may be invalid or expired.');
    },
  });
};

const decline = (id) => {
  router.delete(`/invitations/${id}`, {
    onSuccess: () => {
      toast.success('Invitation declined.');
    },
  });
};
</script>

<template>
  <AuthenticatedLayout title="Invitations">
    <Head title="Invitations" />

    <div class="max-w-3xl mx-auto space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-text font-display">Invitations</h1>
        <p class="text-sm text-text-secondary mt-1">
          Review and respond to workspace invitations.
        </p>
      </div>

      <div v-if="invitations.length === 0" class="rounded-xl border border-border bg-surface p-12 text-center">
        <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-surface-2 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-text-muted">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
          </svg>
        </div>
        <h3 class="text-sm font-semibold text-text mb-1">No pending invitations</h3>
        <p class="text-xs text-text-secondary max-w-sm mx-auto">
          When someone invites you to a workspace, it will appear here.
        </p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="invitation in invitations"
          :key="invitation.id"
          class="rounded-xl border border-border bg-surface p-5 flex items-center justify-between gap-4 shadow-sm"
        >
          <div class="min-w-0 flex-1">
            <h3 class="font-semibold text-text text-sm truncate">
              {{ invitation.workspace_name }}
            </h3>
            <p class="text-xs text-text-secondary mt-0.5">
              Role: <span class="font-medium capitalize">{{ invitation.role }}</span>
              &middot; {{ invitation.created_at }}
            </p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <button
              @click="accept(invitation.token)"
              class="inline-flex items-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-xs font-semibold transition-colors"
            >
              Accept
            </button>
            <button
              @click="decline(invitation.id)"
              class="inline-flex items-center rounded-md border border-border bg-surface hover:bg-surface-2 text-text-secondary px-4 py-2 text-xs font-semibold transition-colors"
            >
              Decline
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
```

### Step 4: Add routes

**File:** `routes/web.php`

Add inside the `auth` middleware group (at the top, BEFORE `scope.workspace` group):

```php
Route::get('/invitations', [\App\Http\Controllers\Web\InvitationWebController::class, 'index'])->name('invitations.index');
Route::post('/invitations/accept', [\App\Http\Controllers\Web\InvitationWebController::class, 'accept'])->name('invitations.accept');
Route::delete('/invitations/{invitation}', [\App\Http\Controllers\Web\InvitationWebController::class, 'decline'])->name('invitations.decline');
```

### Step 5: Run tests

Run: `php artisan test --filter=InvitationWebControllerTest`
Expected: PASS

### Step 6: Commit

```bash
git add app/Http/Controllers/Web/InvitationWebController.php resources/js/Pages/Invitations/Index.vue routes/web.php tests/Feature/Workspace/InvitationWebControllerTest.php
git commit -m "feat: add invitations web page for accepting invites"
```

---

## Task 7: Update `NotificationBell` for invitation notifications (via .notification())

**Files:**
- Modify: `resources/js/Components/NotificationBell.vue`
- Modify: `resources/js/Components/AppNavbar.vue`
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`

**Why `.notification()` and not a custom event:** Laravel's notification `broadcast` channel auto-sends to `App.Models.User.{id}`. Echo's `.notification()` listener catches it. No custom event class needed.

### Step 1: Add `pendingInvitationsCount` shared prop

**File:** `app/Http/Middleware/HandleInertiaRequests.php`

Add to the `share()` method:

```php
'pendingInvitationsCount' => function () use ($request) {
    if (!$request->user()) {
        return 0;
    }
    return \App\Models\Invitation::where('email', $request->user()->email)
        ->where('status', \App\Enums\InviteStatus::Pending)
        ->count();
},
```

### Step 2: Update `NotificationBell.vue`

**File:** `resources/js/Components/NotificationBell.vue`

Add a user-scoped Echo channel with `.notification()` listener in `onMounted()`:

```js
onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('click', closeDropdown);
    }

    // User-scoped channel for invitation notifications
    // (separate from workspace channel — user may not be in workspace yet)
    const userChannel = window.Echo.private(`App.Models.User.${currentUserId.value}`);

    userChannel.notification((notification) => {
        if (notification.type && notification.type.includes('WorkspaceInvitation')) {
            handleNewNotification(
                `You've been invited to join "${notification.workspace_name}" as ${notification.role}.`,
                'invitation'
            );
        }
    });

    // Existing workspace channel listeners...
    if (window.Echo) {
        channel = window.Echo.private(`workspace.${props.workspaceId}`);
        channel.listen('TaskMoved', (e) => { ... });
        channel.listen('TaskAssigned', (e) => { ... });
        channel.listen('TaskCommented', (e) => { ... });
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('click', closeDropdown);
    }
    if (channel) {
        channel.stopListening('TaskMoved');
        channel.stopListening('TaskAssigned');
        channel.stopListening('TaskCommented');
        channel = null;
    }
    // User channel cleanup is handled by Echo.leave() automatically on unmount
});
```

Add `invitation` type styling to icon helpers:

```js
const getIconBgClass = (type) => {
    if (type === 'moved') return 'bg-blue-50/80 dark:bg-blue-950/20 border border-blue-100/80 dark:border-blue-900/40';
    if (type === 'assigned') return 'bg-emerald-50/80 dark:bg-emerald-950/20 border border-emerald-100/80 dark:border-emerald-900/40';
    if (type === 'invitation') return 'bg-amber-50/80 dark:bg-amber-950/20 border border-amber-100/80 dark:border-amber-900/40';
    return 'bg-purple-50/80 dark:bg-purple-950/20 border border-purple-100/80 dark:border-purple-900/40';
};

const getIconColorClass = (type) => {
    if (type === 'moved') return 'text-blue-600 dark:text-blue-400';
    if (type === 'assigned') return 'text-emerald-600 dark:text-emerald-400';
    if (type === 'invitation') return 'text-amber-600 dark:text-amber-400';
    return 'text-purple-600 dark:text-purple-400';
};
```

Add invitation SVG icon in the template icon chain:

```vue
<!-- Invitation -->
<svg v-else-if="notification.type === 'invitation'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4" :class="getIconColorClass(notification.type)">
    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
</svg>
```

Update the conditional chain — change existing `v-else` to `v-else-if`:

```vue
<svg v-if="notification.type === 'moved'" ...>...</svg>
<svg v-else-if="notification.type === 'assigned'" ...>...</svg>
<svg v-else-if="notification.type === 'invitation'" ...>...</svg>
<svg v-else ...>...</svg>
```

### Step 3: Update `AppNavbar.vue` with invitations link

**File:** `resources/js/Components/AppNavbar.vue`

Add computed for pending count:

```js
const pendingInvitations = computed(() => page.props.pendingInvitationsCount ?? 0);
```

Pass it to `NotificationBell`:

```vue
<NotificationBell
  v-if="currentWorkspace"
  :workspace-id="currentWorkspace.id"
  :pending-invitations-count="pendingInvitations"
/>
```

Import `Link` in the script (already used but ensure it's imported):

```js
import { computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
```

Add "Invitations" menu item in the user `DropdownMenu`, before Sign Out:

```vue
<DropdownMenuItem as-child>
  <Link
    href="/invitations"
    class="flex items-center w-full px-3 py-2.5 text-xs font-semibold text-text rounded-lg hover:bg-surface-3 transition-colors cursor-pointer"
  >
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
    </svg>
    Invitations
    <span v-if="pendingInvitations > 0" class="ml-auto bg-primary text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
      {{ pendingInvitations }}
    </span>
  </Link>
</DropdownMenuItem>
```

### Step 4: Rebuild frontend

Run: `pnpm run build`
Expected: No errors

### Step 5: Commit

```bash
git add resources/js/Components/NotificationBell.vue resources/js/Components/AppNavbar.vue app/Http/Middleware/HandleInertiaRequests.php
git commit -m "feat: add invitation notifications to bell + user dropdown"
```

---

## Task 8: Update `RegisterController` to use `intended()` redirect

**Files:**
- Modify: `app/Http/Controllers/Web/Auth/RegisterController.php`

This preserves the `?token=` query param when a user clicks an invitation link, is redirected to register, and needs to land back on the invitation page.

### Step 1: Change redirect

**File:** `app/Http/Controllers/Web/Auth/RegisterController.php` — line 40:

Change:
```php
return redirect('/dashboard');
```

To:
```php
return redirect()->intended('/dashboard');
```

### Step 2: Commit

```bash
git add app/Http/Controllers/Web/Auth/RegisterController.php
git commit -m "fix: use intended() redirect to preserve invitation token after registration"
```

---

## Full test suite verification

Run all invitation-related tests:

```bash
php artisan test --filter=WorkspaceInvitation
php artisan test --filter=InviteMember
php artisan test --filter=PendingInvitation
php artisan test --filter=InvitationWeb
```

Expected: All PASS.

Then run the full test suite:

```bash
php artisan test
```

Expected: All PASS (no regressions).

---

## Summary of all files

### Created (8 files):
| File | Purpose |
|------|---------|
| `database/factories/InvitationFactory.php` | Factory for tests |
| `app/Notifications/WorkspaceInvitation.php` | Mail + database + broadcast notification |
| `app/Http/Resources/InvitationResource.php` | API resource (excludes token) |
| `app/Http/Controllers/Web/InvitationWebController.php` | Web controller for listing/accepting/declining invites |
| `resources/js/Pages/Invitations/Index.vue` | Vue page for viewing/responding to invitations |
| `tests/Unit/Notifications/WorkspaceInvitationTest.php` | Notification unit tests |
| `tests/Feature/Workspace/PendingInvitationsTest.php` | API pending list tests |
| `tests/Feature/Workspace/InvitationWebControllerTest.php` | Web invite flow tests |

### Modified (7 files):
| File | Changes |
|------|---------|
| `app/Actions/InviteMemberAction.php` | Added 4th param `User $inviter`, deletes all statuses (not just Pending), on-demand notifications for unregistered emails |
| `app/Http/Controllers/Api/V1/InvitationController.php` | Passes `$request->user()`, removes token from response, adds `pending()` |
| `app/Http/Controllers/Web/WorkspaceController.php` | Passes `$request->user()` to action |
| `app/Http/Controllers/Web/Auth/RegisterController.php` | Changed to `redirect()->intended('/dashboard')` |
| `app/Http/Middleware/HandleInertiaRequests.php` | Shares `pendingInvitationsCount` |
| `routes/api.php` | Added `GET /api/v1/invitations/pending` |
| `routes/web.php` | Added `GET /invitations`, `POST /invitations/accept`, `DELETE /invitations/{invitation}` |
| `resources/js/Components/NotificationBell.vue` | User-channel `.notification()` listener for invitations, invitation type styling |
| `resources/js/Components/AppNavbar.vue` | Invitations link in user dropdown with badge |
| `tests/Unit/Actions/InviteMemberActionTest.php` | Assert notification sent, re-invite after decline doesn't crash |
| `tests/Feature/Workspace/InviteMemberTest.php` | Assert token removed from API response |

### "Removed" compared to v1 (2 files):
| File | Reason |
|------|--------|
| `app/Events/InvitationCreated.php` | Redundant — notification broadcast channel already handles this |
| `tests/Unit/Events/InvitationCreatedTest.php` | Redundant |
