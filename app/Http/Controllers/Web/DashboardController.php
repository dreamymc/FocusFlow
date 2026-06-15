<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Workspace;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Find current workspace
        $workspaceId = session('current_workspace_id');
        $workspace = null;
        if ($workspaceId) {
            $workspace = $user->workspaces()->find($workspaceId);
        }
        if (!$workspace) {
            $workspace = $user->workspaces()->first();
            if ($workspace) {
                session(['current_workspace_id' => $workspace->id]);
            }
        }

        if (!$workspace) {
            return Inertia::render('Dashboard', [
                'stats' => null,
                'recentTasks' => [],
            ]);
        }

        // Stats queries scoped to current workspace and assigned to current user
        $workspaceTasksQuery = Task::forWorkspace($workspace)->assignedTo($user);

        $totalTasks = (clone $workspaceTasksQuery)->count();
        
        $activeTasks = (clone $workspaceTasksQuery)
            ->whereIn('status', [TaskStatus::InProgress->value, TaskStatus::InReview->value])
            ->count();
            
        $completedToday = (clone $workspaceTasksQuery)
            ->where('status', TaskStatus::Done->value)
            ->whereDate('updated_at', today())
            ->count();

        // Recent tasks - last 5 tasks assigned to the user in this workspace
        $recentTasks = Task::forWorkspace($workspace)
            ->assignedTo($user)
            ->with(['project'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'status' => $t->status->value,
                'status_label' => $t->status->label(),
                'project_name' => $t->project?->name ?? 'No Project',
                'project_id' => $t->project_id,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalTasks' => $totalTasks,
                'activeTasks' => $activeTasks,
                'completedToday' => $completedToday,
            ],
            'recentTasks' => $recentTasks,
        ]);
    }
}
