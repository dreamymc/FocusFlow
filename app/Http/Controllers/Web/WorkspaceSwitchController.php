<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class WorkspaceSwitchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'workspace_id' => ['required', 'integer', 'exists:workspaces,id'],
        ]);

        // Verify the user is member of this workspace
        if (!$request->user()->workspaces()->where('workspaces.id', $validated['workspace_id'])->exists()) {
            abort(403, 'Unauthorized workspace access.');
        }

        session(['current_workspace_id' => $validated['workspace_id']]);

        return back()->with('success', 'Workspace switched successfully.');
    }
}
