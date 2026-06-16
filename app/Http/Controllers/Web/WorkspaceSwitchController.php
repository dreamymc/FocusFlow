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

        $targetWorkspaceId = (int) $validated['workspace_id'];
        session(['current_workspace_id' => $targetWorkspaceId]);

        $previousUrl = url()->previous();
        $parsedUrl = parse_url($previousUrl);
        $path = $parsedUrl['path'] ?? '';

        // Match /workspaces/{id}(/.*)?
        if (preg_match('#^/workspaces/(\d+)(/.*)?$#', $path, $matches)) {
            $oldWorkspaceId = (int) $matches[1];
            $subPath = $matches[2] ?? '';

            if ($oldWorkspaceId !== $targetWorkspaceId) {
                // If subpath is a specific project (e.g. /projects/123) we redirect to projects index
                if (preg_match('#^/projects/\d+#', $subPath)) {
                    $newPath = "/workspaces/{$targetWorkspaceId}/projects";
                } else {
                    $newPath = "/workspaces/{$targetWorkspaceId}" . $subPath;
                }

                // Reconstruct the URL
                $newUrl = ($parsedUrl['scheme'] ?? 'http') . '://' . ($parsedUrl['host'] ?? 'localhost');
                if (isset($parsedUrl['port'])) {
                    $newUrl .= ':' . $parsedUrl['port'];
                }
                $newUrl .= $newPath;
                if (isset($parsedUrl['query'])) {
                    $newUrl .= '?' . $parsedUrl['query'];
                }

                return redirect($newUrl)->with('success', 'Workspace switched successfully.');
            }
        }

        return back()->with('success', 'Workspace switched successfully.');
    }
}
