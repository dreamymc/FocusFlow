<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'workspace_id',
        'title',
        'description',
        'status',
        'priority',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User, $this>
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Label, $this>
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'task_label')
            ->withTimestamps();
    }

    public function scopeForWorkspace(Builder $query, Workspace|int $workspace): Builder
    {
        $id = $workspace instanceof Workspace ? $workspace->id : $workspace;
        return $query->where('workspace_id', $id);
    }

    public function scopeAssignedTo(Builder $query, User|int $user): Builder
    {
        $id = $user instanceof User ? $user->id : $user;
        return $query->whereHas('assignees', function (Builder $q) use ($id) {
            $q->where('users.id', $id);
        });
    }

    public function scopeByStatus(Builder $query, TaskStatus|string $status): Builder
    {
        $value = $status instanceof TaskStatus ? $status->value : $status;
        return $query->where('status', $value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Comment, $this>
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');
    }
}
