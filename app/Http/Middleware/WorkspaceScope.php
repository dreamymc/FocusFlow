<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;

class WorkspaceScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->route('workspace');

        if (!$workspace) {
            return $next($request);
        }

        if (!$workspace instanceof Workspace) {
            $workspace = Workspace::findOrFail($workspace);
        }

        $user = $request->user();
        if (!$user || !$user->workspaces()->where('workspaces.id', $workspace->id)->exists()) {
            abort(403, 'You do not have access to this workspace.');
        }

        app()->instance(Workspace::class, $workspace);

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            setPermissionsTeamId($workspace->id);
        }

        return $next($request);
    }
}
