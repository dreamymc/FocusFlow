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

    protected $table = 'workspace_invites';

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
}
