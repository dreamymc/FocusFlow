<?php

namespace App\Http\Requests;

use App\Enums\WorkspaceRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');
        $pivot = $this->user()->workspaces()->where('workspaces.id', $workspace->id)->first()?->pivot;
        
        return $pivot && $pivot->getAttribute('role') === WorkspaceRole::Admin->value;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role' => ['required', new Enum(WorkspaceRole::class)],
        ];
    }
}
