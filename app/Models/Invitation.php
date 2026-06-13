<?php

namespace App\Models;

use App\Enums\InviteStatus;
use App\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'email',
        'role',
        'token',
        'status',
    ];

    protected $casts = [
        'status' => InviteStatus::class,
        'role' => WorkspaceRole::class,
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeForWorkspace(\Illuminate\Database\Eloquent\Builder $query, Workspace|int $workspace): \Illuminate\Database\Eloquent\Builder
    {
        $id = $workspace instanceof Workspace ? $workspace->id : $workspace;
        return $query->where('workspace_id', $id);
    }
}
