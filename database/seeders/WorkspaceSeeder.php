<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Label;
use App\Models\Comment;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Enums\WorkspaceRole;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Hash;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workspaceId = 3;
        $workspace = Workspace::find($workspaceId);

        if (!$workspace) {
            $this->command->error("Workspace with ID {$workspaceId} not found!");
            return;
        }

        $this->command->info("Seeding workspace: {$workspace->name} (ID: {$workspaceId})");

        // 1. Get or create users to invite to the workspace
        $owner = User::where('email', 'mbl@example.com')->first();
        if (!$owner) {
            $this->command->error("Owner user mbl@example.com not found!");
            return;
        }

        // Get or create standard team members
        $sarah = User::firstOrCreate(
            ['email' => 'sarah@focusflow.app'],
            ['name' => 'Sarah Connor', 'password' => Hash::make('password')]
        );

        $alex = User::firstOrCreate(
            ['email' => 'alex@focusflow.app'],
            ['name' => 'Alex Rivera', 'password' => Hash::make('password')]
        );

        $jessica = User::firstOrCreate(
            ['email' => 'jessica@focusflow.app'],
            ['name' => 'Jessica Chen', 'password' => Hash::make('password')]
        );

        $marcus = User::firstOrCreate(
            ['email' => 'marcus@focusflow.app'],
            ['name' => 'Marcus Vance', 'password' => Hash::make('password')]
        );

        // Attach users to the workspace if not already attached
        $registrar = app(PermissionRegistrar::class);

        $attachUser = function(Workspace $workspace, User $user, WorkspaceRole $role) use ($registrar) {
            if (!$workspace->users()->where('user_id', $user->id)->exists()) {
                $workspace->users()->attach($user->id, ['role' => $role->value]);
            }
            
            // Set Spatie permissions team
            $registrar->setPermissionsTeamId($workspace->id);
            $roleModel = Role::findOrCreate($role->value, 'web');
            $user->assignRole($roleModel);
        };

        $attachUser($workspace, $sarah, WorkspaceRole::Member);
        $attachUser($workspace, $alex, WorkspaceRole::Member);
        $attachUser($workspace, $jessica, WorkspaceRole::Member);
        $attachUser($workspace, $marcus, WorkspaceRole::Viewer);

        // 2. Create labels for this workspace
        $labelsData = [
            'Bug' => '#ef4444',
            'Feature' => '#3b82f6',
            'Design' => '#ec4899',
            'API Integration' => '#8b5cf6',
            'Marketing' => '#10b981',
            'Critical' => '#f59e0b',
            'UI/UX' => '#ec4899',
        ];

        $labels = [];
        foreach ($labelsData as $name => $color) {
            $labels[$name] = Label::firstOrCreate([
                'workspace_id' => $workspace->id,
                'name' => $name,
            ], [
                'color' => $color,
            ]);
        }

        // 3. Create 3 Projects in this workspace
        $projectPortal = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Client Portal Development',
            'description' => 'Build a secure, client-facing dashboard for viewing invoices, sharing documents, and tracking project status.',
        ]);

        $projectMarketing = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Marketing Campaign Q3',
            'description' => 'Plan and execute Q3 outreach, including SEO optimization, email campaigns, and video pitches.',
        ]);

        $projectInfra = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Infrastructure Migration',
            'description' => 'Migrate critical cache storage and background worker servers to high-availability environments.',
        ]);

        // 4. Create tasks for Client Portal Development
        
        // Task 1: Backlog
        $t1 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectPortal->id,
            'title' => 'Configure OAuth2 Login Providers',
            'description' => "Configure social login authentication flow for clients (Google, GitHub) using Laravel Socialite.\n\nEnsure that newly registered users are assigned the appropriate viewer roles in the tenant space.",
            'status' => TaskStatus::Backlog,
            'priority' => TaskPriority::Medium,
        ]);
        $t1->assignees()->attach([$alex->id]);
        $t1->labels()->attach([$labels['Feature']->id, $labels['API Integration']->id]);

        // Task 2: Backlog
        $t2 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectPortal->id,
            'title' => 'Export Invoice to PDF',
            'description' => 'Add an download option to invoice cards generating PDF prints using standard layout libraries.',
            'status' => TaskStatus::Backlog,
            'priority' => TaskPriority::Low,
        ]);
        $t2->assignees()->attach([$sarah->id]);
        $t2->labels()->attach([$labels['Feature']->id]);

        // Task 3: In Progress
        $t3 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectPortal->id,
            'title' => 'Design Dashboard UI Components',
            'description' => 'Build reactive stats widgets and progress bars for tracking clients engagement milestones.',
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::High,
        ]);
        $t3->assignees()->attach([$jessica->id]);
        $t3->labels()->attach([$labels['Design']->id, $labels['UI/UX']->id]);

        Comment::create([
            'task_id' => $t3->id,
            'user_id' => $jessica->id,
            'content' => "I've uploaded the dashboard wireframes to Figma. Starting development of the Vue widgets.",
        ]);
        Comment::create([
            'task_id' => $t3->id,
            'user_id' => $owner->id,
            'content' => "Contrast on the sidebar nav active state looks a bit low in dark mode, let's make sure it complies with WCAG guidelines.",
        ]);

        // Task 4: In Progress
        $t4 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectPortal->id,
            'title' => 'Stripe Checkout Session Setup',
            'description' => 'Implement Stripe Checkout redirect flow for invoice payments and handle successful charge webhooks.',
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::High,
        ]);
        $t4->assignees()->attach([$owner->id]);
        $t4->labels()->attach([$labels['Feature']->id, $labels['API Integration']->id]);

        Comment::create([
            'task_id' => $t4->id,
            'user_id' => $owner->id,
            'content' => "Integrating Stripe customer billing portal redirection. Testing webhooks locally using Stripe CLI.",
        ]);

        // Task 5: In Review
        $t5 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectPortal->id,
            'title' => 'Document API Endpoints',
            'description' => 'Draft comprehensive documentation for external integration endpoints including authentication steps and response schemas.',
            'status' => TaskStatus::InReview,
            'priority' => TaskPriority::Medium,
        ]);
        $t5->assignees()->attach([$alex->id]);

        Comment::create([
            'task_id' => $t5->id,
            'user_id' => $alex->id,
            'content' => "All core endpoints are documented in docs/API.md. Ready for verification.",
        ]);
        Comment::create([
            'task_id' => $t5->id,
            'user_id' => $sarah->id,
            'content' => "Looks very comprehensive! Good work Alex.",
        ]);

        // Task 6: Done
        $t6 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectPortal->id,
            'title' => 'Setup PostgreSQL Database Schema',
            'description' => 'Write migrations for Client database models, indexes, and unique constraints.',
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::High,
        ]);
        $t6->assignees()->attach([$owner->id]);

        // 5. Create tasks for Marketing Campaign
        
        // Task 7: Backlog
        $t7 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectMarketing->id,
            'title' => 'SEO Keywords Analysis',
            'description' => 'Perform market analysis on competitor keywords and compile content plan.',
            'status' => TaskStatus::Backlog,
            'priority' => TaskPriority::Low,
        ]);
        $t7->assignees()->attach([$marcus->id]);
        $t7->labels()->attach([$labels['Marketing']->id]);

        // Task 8: In Progress
        $t8 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectMarketing->id,
            'title' => 'Landing Page Copywriting',
            'description' => 'Draft high-converting copy targeting managers looking to optimize project delivery workflows.',
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::High,
        ]);
        $t8->assignees()->attach([$sarah->id]);
        $t8->labels()->attach([$labels['Marketing']->id]);

        Comment::create([
            'task_id' => $t8->id,
            'user_id' => $sarah->id,
            'content' => "First draft ready for review in Drive. Focus is on clear value propositions.",
        ]);

        // Task 9: Done
        $t9 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectMarketing->id,
            'title' => 'Create FocusFlow Pitch Deck',
            'description' => 'Design visual slides illustrating main software advantages, pricing models, and team features.',
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::Medium,
        ]);
        $t9->assignees()->attach([$jessica->id]);
        $t9->labels()->attach([$labels['Marketing']->id, $labels['Design']->id]);

        // 6. Create tasks for Infrastructure Migration
        
        // Task 10: Backlog
        $t10 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectInfra->id,
            'title' => 'Migrate Redis to AWS ElastiCache',
            'description' => 'Configure redis replica clusters, configure caching configs in config/database.php.',
            'status' => TaskStatus::Backlog,
            'priority' => TaskPriority::High,
        ]);
        $t10->assignees()->attach([$alex->id]);
        $t10->labels()->attach([$labels['Critical']->id]);

        // Task 11: In Review
        $t11 = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectInfra->id,
            'title' => 'Configure Horizon Supervisor',
            'description' => 'Configure background workers daemon configs inside production server supervisors.',
            'status' => TaskStatus::InReview,
            'priority' => TaskPriority::High,
        ]);
        $t11->assignees()->attach([$owner->id]);
        $t11->labels()->attach([$labels['Critical']->id]);

        Comment::create([
            'task_id' => $t11->id,
            'user_id' => $owner->id,
            'content' => "Horizon config tested in staging. Handlers balance the queue queues load perfectly.",
        ]);

        $this->command->info("Workspace populated successfully with sample data!");
    }
}
