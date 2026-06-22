<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Actions\CreateWorkspaceAction;
use App\Actions\InviteMemberAction;
use App\Actions\UpdateWorkspaceAction;
use App\Enums\WorkspaceRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function create()
    {
        return Inertia::render('Workspaces/Create');
    }

    public function store(Request $request, CreateWorkspaceAction $createWorkspaceAction)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace = $createWorkspaceAction->execute(
            $validated['name'],
            $request->user()
        );

        session(['current_workspace_id' => $workspace->id]);

        return redirect()->route('dashboard')->with('success', 'Workspace created!');
    }

    public function settings(Request $request, Workspace $workspace)
    {
        if (!$request->user()->hasRole(WorkspaceRole::Admin->value)) {
            abort(403, 'Only workspace admins can access settings.');
        }

        return Inertia::render('Workspaces/Settings', [
            'workspace' => $workspace,
            'members' => $workspace->users()->withPivot('role')->get()->map(function (\App\Models\User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                ];
            }),
        ]);
    }

    public function update(Request $request, Workspace $workspace, UpdateWorkspaceAction $updateWorkspaceAction)
    {
        if (!$request->user()->hasRole(WorkspaceRole::Admin->value)) {
            abort(403, 'Only workspace admins can update workspace.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $updateWorkspaceAction->execute($workspace, $validated['name']);

        return redirect()->route('workspaces.settings', $workspace)->with('success', 'Workspace updated successfully.');
    }

    public function invite(Request $request, Workspace $workspace, InviteMemberAction $inviteMemberAction)
    {
        if (!$request->user()->hasRole(WorkspaceRole::Admin->value)) {
            abort(403, 'Only workspace admins can invite members.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'string', 'in:member,viewer'],
        ]);

        $inviteMemberAction->execute(
            $workspace,
            $validated['email'],
            WorkspaceRole::from($validated['role']),
            $request->user()
        );

        return redirect()->route('workspaces.settings', $workspace)->with('success', "Invitation sent to {$validated['email']}.");
    }
}
