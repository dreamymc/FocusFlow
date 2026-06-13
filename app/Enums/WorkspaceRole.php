<?php

namespace App\Enums;

enum WorkspaceRole: string
{
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match($this) {
            self::Admin => 'Admin',
            self::Member => 'Member',
            self::Viewer => 'Viewer',
        };
    }
}
