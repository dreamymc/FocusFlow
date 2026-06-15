<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn () => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'workspaces' => fn () => $request->user()
                ? $request->user()->workspaces()->select('workspaces.id', 'workspaces.name')->get()->map(fn($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                    'slug' => $w->id,
                ])
                : [],
            'currentWorkspace' => function () use ($request) {
                if (!$request->user()) {
                    return null;
                }
                $workspaceId = session('current_workspace_id');
                $workspace = null;
                if ($workspaceId) {
                    $workspace = $request->user()->workspaces()->with('projects')->find($workspaceId);
                }
                if (!$workspace) {
                    $workspace = $request->user()->workspaces()->with('projects')->first();
                    if ($workspace) {
                        session(['current_workspace_id' => $workspace->id]);
                    }
                }
                return $workspace ? [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                    'projects' => $workspace->projects->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                    ]),
                ] : null;
            },
            'userRole' => function () use ($request) {
                if (!$request->user()) {
                    return null;
                }
                $workspaceId = session('current_workspace_id') ?: $request->user()->workspaces()->first()?->id;
                if (!$workspaceId) {
                    return null;
                }
                $workspaceUser = \Illuminate\Support\Facades\DB::table('workspace_user')
                    ->where('workspace_id', $workspaceId)
                    ->where('user_id', $request->user()->id)
                    ->first();
                return $workspaceUser?->role;
            },
            'plan' => function () use ($request) {
                if (!$request->user()) {
                    return 'free';
                }
                $workspaceId = session('current_workspace_id') ?: $request->user()->workspaces()->first()?->id;
                if (!$workspaceId) {
                    return 'free';
                }
                $workspace = $request->user()->workspaces()->find($workspaceId);
                return ($workspace && $workspace->subscribed('default')) ? 'pro' : 'free';
            },
        ]);
    }
}
