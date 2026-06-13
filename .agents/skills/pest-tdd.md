---
name: pest-tdd
description: >
  Pest PHP testing patterns for Laravel 11. Red-green-refactor cycle.
  Feature tests for HTTP, unit tests for Actions. Always fail first.
compatible_agents:
  - tdd-engineer
---

# Pest TDD Skill

## The Only Acceptable Test Cycle
```
1. Write test → confirm it FAILS (red) → commit
2. Implement minimum code to pass → confirm it PASSES (green) → commit
3. Refactor without breaking → re-run tests → commit
```

## Setup
```php
// tests/Pest.php
<?php
uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

uses(Tests\TestCase::class)
    ->in('Unit');
```

## Feature Test Anatomy
```php
// Always use RefreshDatabase (via Pest.php uses())
// Always use factories for test data
// Always assert both the HTTP response AND the database state

it('creates a project', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    $workspace->addMember($user, WorkspaceRole::Admin);

    $response = $this->actingAs($user)
        ->withToken($user->createToken('test')->plainTextToken)
        ->postJson("/api/v1/workspaces/{$workspace->id}/projects", [
            'name' => 'Sprint 1',
        ]);

    $response->assertCreated()
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('data.id')
                 ->where('data.name', 'Sprint 1')
                 ->etc()
        );

    $this->assertDatabaseHas('projects', [
        'name' => 'Sprint 1',
        'workspace_id' => $workspace->id,
    ]);
});
```

## Parallel Test Safety
```bash
php artisan test --parallel   # Run test suite in parallel for speed
```

Always use `RefreshDatabase` (not `DatabaseTransactions`) when running in parallel.

## Coverage Command
```bash
php artisan test --coverage --min=80
```

---
---
name: websockets-reverb
description: >
  Laravel Reverb WebSocket patterns. Channel auth, event broadcasting, Echo frontend.
  Load before any real-time feature work.
compatible_agents:
  - backend-engineer
  - integration-specialist
---

# WebSockets & Reverb Skill

## Broadcasting Setup
```php
// app/Events/TaskMoved.php
class TaskMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Task $task,
        public readonly TaskStatus $previousStatus,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("workspace.{$this->task->workspace_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.moved';  // Frontend listens for this event name
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'new_status' => $this->task->status->value,
            'previous_status' => $this->previousStatus->value,
        ];
    }
}
```

## Channel Authorization
```php
// routes/channels.php
Broadcast::channel('workspace.{workspaceId}', function (User $user, int $workspaceId) {
    return $user->workspaces()->where('workspaces.id', $workspaceId)->exists();
});
```

## Frontend Echo
```javascript
// resources/js/composables/useTaskUpdates.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
});

export function useWorkspaceChannel(workspaceId) {
    const channel = window.Echo.private(`workspace.${workspaceId}`);
    channel.listen('.task.moved', (data) => {
        // Update reactive state
    });
    return { channel };
}
```

## Testing Broadcasts
```php
it('broadcasts TaskMoved when a task status changes', function () {
    Event::fake([TaskMoved::class]);

    $task = Task::factory()->create(['status' => TaskStatus::Backlog]);

    app(MoveTaskAction::class)->execute($task, TaskStatus::InProgress);

    Event::assertDispatched(TaskMoved::class, function ($event) use ($task) {
        return $event->task->id === $task->id
            && $event->previousStatus === TaskStatus::Backlog;
    });
});
```

---
---
name: api-design
description: >
  REST API design patterns for Laravel 11. Versioning, resources, rate limiting, filtering.
  Load before building any API controller or route.
compatible_agents:
  - backend-engineer
  - architect
---

# API Design Skill

## Route Structure
```php
// routes/api.php
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('workspaces/{workspace}')
        ->middleware('scope.workspace')  // WorkspaceScope middleware
        ->group(function () {
            Route::apiResource('projects', ProjectController::class);
            Route::apiResource('projects.tasks', TaskController::class);
            Route::put('tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');
        });
});
```

## Rate Limiting
```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Stricter limit for auth endpoints
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

## Filtering Pattern
```php
// app/Http/Controllers/Api/V1/TaskController.php
public function index(IndexTaskRequest $request, Workspace $workspace): TaskCollection
{
    $tasks = Task::query()
        ->forWorkspace($workspace)
        ->when($request->validated('status'), fn($q, $s) => $q->where('status', $s))
        ->when($request->validated('assignee'), fn($q, $a) => $q->where('assignee_id', $a))
        ->when($request->validated('sort'), fn($q, $s) => $q->orderBy($s))
        ->with(['assignee', 'labels'])
        ->paginate(15);

    return new TaskCollection($tasks);
}
```

## Consistent Error Handling
```php
// app/Exceptions/Handler.php — render method
public function render($request, Throwable $e): Response
{
    if ($request->expectsJson()) {
        return match(true) {
            $e instanceof ModelNotFoundException => response()->json(['message' => 'Not found'], 404),
            $e instanceof AuthorizationException => response()->json(['message' => 'Forbidden'], 403),
            $e instanceof ValidationException => response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422),
            default => response()->json(['message' => 'Server error'], 500),
        };
    }
    return parent::render($request, $e);
}
```

## API Resource Example
```php
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'priority' => $this->priority->value,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
```
