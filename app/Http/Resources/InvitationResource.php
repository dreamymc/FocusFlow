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
