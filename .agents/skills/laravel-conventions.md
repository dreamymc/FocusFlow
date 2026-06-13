---
name: laravel-conventions
description: >
  Laravel 11 + PHP 8.3 architectural patterns, naming conventions, and anti-patterns.
  Load this skill before writing ANY PHP code in this project.
compatible_agents:
  - backend-engineer
  - tdd-engineer
  - architect
source: https://mcpmarket.com/tools/skills/laravel-11-coding-standards
---

# Laravel 11 Conventions Skill

## Architecture Rules

### Directory Ownership
| Layer | Directory | Responsibility |
|-------|-----------|----------------|
| HTTP | `app/Http/Controllers/Api/V1/` | Route handling only — call Actions |
| Validation | `app/Http/Requests/` | One FormRequest per action |
| Transformation | `app/Http/Resources/` | API output shaping |
| Business Logic | `app/Actions/` | One class, one use case |
| Domain Services | `app/Services/` | Stateful, reusable domain services |
| Data | `app/DTOs/` | Readonly PHP 8.3 data classes |
| Types | `app/Enums/` | Backed enums only (string or int) |
| Events | `app/Events/` | Named after past tense domain events |
| Side Effects | `app/Listeners/` | Queued, decoupled from events |

### Naming Conventions
- Controllers: `TaskController` (resource-named, never `TaskApiController`)
- Actions: `[Verb][Noun]Action` → `CreateTaskAction`, `MoveTaskAction`
- Events: past tense → `TaskCompleted`, `MemberInvited`
- Listeners: gerund → `SendSlackNotification`, `NotifyAssignee`
- Requests: `[Store|Update][Resource]Request` → `StoreTaskRequest`
- Resources: `[Resource]Resource`, `[Resource]Collection`
- Enums: PascalCase class, TitleCase values → `TaskStatus::InProgress`

### Model Rules
```php
class Task extends Model
{
    // ALWAYS define fillable
    protected $fillable = ['title', 'status', 'priority', 'assignee_id', 'workspace_id'];

    // ALWAYS cast enums
    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    // ALWAYS define workspace scope
    public function scopeForWorkspace(Builder $query, Workspace $workspace): Builder
    {
        return $query->where('workspace_id', $workspace->id);
    }
}
```

### Eloquent Anti-Patterns
```php
// ❌ NEVER — N+1 query
$tasks = Task::all();
foreach ($tasks as $task) {
    echo $task->assignee->name; // N queries
}

// ✅ ALWAYS — eager load
$tasks = Task::with('assignee')->forWorkspace($workspace)->get();

// ❌ NEVER — no scope
$tasks = Task::where('workspace_id', $workspace->id)->get();

// ✅ ALWAYS — use scope
$tasks = Task::forWorkspace($workspace)->get();
```

## PHP 8.3 Patterns

### Readonly DTOs
```php
final readonly class CreateTaskData
{
    public function __construct(
        public string $title,
        public TaskPriority $priority,
        public ?int $assigneeId = null,
    ) {}

    public static function fromRequest(StoreTaskRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            priority: TaskPriority::from($request->validated('priority')),
            assigneeId: $request->validated('assignee_id'),
        );
    }
}
```

### Backed Enums
```php
enum TaskStatus: string
{
    case Backlog = 'backlog';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Done = 'done';

    public function label(): string
    {
        return match($this) {
            self::Backlog => 'Backlog',
            self::InProgress => 'In Progress',
            self::InReview => 'In Review',
            self::Done => 'Done',
        };
    }
}
```

## API Response Standards
All API responses must follow this structure:
```json
{
  "data": { ... },          // Single resource
  "meta": {                  // For collections
    "current_page": 1,
    "total": 42
  },
  "links": {
    "next": "...",
    "prev": null
  }
}
```

Error responses:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

## Anti-Patterns (NEVER do these)
- ❌ `$request->all()` → use `$request->validated()`
- ❌ Logic in controllers → move to Actions
- ❌ `User::all()` → always scope
- ❌ Raw SQL strings → use query builder or Eloquent
- ❌ `->toArray()` in API responses → use Resource classes
- ❌ `dd()`, `dump()` in committed code
- ❌ Events fired from controllers → fire from Actions
- ❌ `$guarded = []` on models → always use `$fillable`
