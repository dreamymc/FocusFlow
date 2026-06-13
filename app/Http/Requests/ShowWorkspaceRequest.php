<?php

namespace App\Http\Requests;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;

class ShowWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Workspace $workspace */
        $workspace = $this->route('workspace');
        
        return $this->user()->workspaces()->where('workspaces.id', $workspace->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
