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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clean up existing records except the primary developer user to avoid duplicated records
        // But keep m.logronio.536468@umindanao.edu.ph intact if they exist
        $primaryEmail = 'm.logronio.536468@umindanao.edu.ph';
        $primaryUser = User::where('email', $primaryEmail)->first();

        // Truncate dependent tables in correct order
        DB::statement('TRUNCATE comments CASCADE');
        DB::statement('TRUNCATE task_assignees CASCADE');
        DB::statement('TRUNCATE task_label CASCADE');
        DB::statement('TRUNCATE tasks CASCADE');
        DB::statement('TRUNCATE labels CASCADE');
        DB::statement('TRUNCATE projects CASCADE');
        DB::statement('TRUNCATE workspace_user CASCADE');
        
        // Delete workspaces
        Workspace::query()->delete();
        
        // Delete users except primary
        if ($primaryUser) {
            User::where('id', '!=', $primaryUser->id)->delete();
        } else {
            User::query()->delete();
            // Create primary user
            $primaryUser = User::factory()->create([
                'name' => 'Mc Bernard Logronio',
                'email' => $primaryEmail,
                'password' => Hash::make('password'),
            ]);
        }

        // Create standard team users
        $sarah = User::factory()->create([
            'name' => 'Sarah Connor',
            'email' => 'sarah@focusflow.app',
            'password' => Hash::make('password'),
        ]);

        $alex = User::factory()->create([
            'name' => 'Alex Rivera',
            'email' => 'alex@focusflow.app',
            'password' => Hash::make('password'),
        ]);

        $jessica = User::factory()->create([
            'name' => 'Jessica Chen',
            'email' => 'jessica@focusflow.app',
            'password' => Hash::make('password'),
        ]);

        $marcus = User::factory()->create([
            'name' => 'Marcus Vance',
            'email' => 'marcus@focusflow.app',
            'password' => Hash::make('password'),
        ]);

        // 2. Create workspaces
        $workspaceCamp = Workspace::factory()->create([
            'name' => 'MCBERNARD CAMP',
        ]);

        $workspaceRussel = Workspace::factory()->create([
            'name' => 'Vance Russel LLC',
        ]);

        // Attach users to MCBERNARD CAMP
        $workspaceCamp->users()->attach($primaryUser->id, ['role' => 'admin']);
        $workspaceCamp->users()->attach($sarah->id, ['role' => 'admin']);
        $workspaceCamp->users()->attach($alex->id, ['role' => 'member']);
        $workspaceCamp->users()->attach($jessica->id, ['role' => 'member']);
        $workspaceCamp->users()->attach($marcus->id, ['role' => 'viewer']);

        // Attach users to Vance Russel LLC
        $workspaceRussel->users()->attach($primaryUser->id, ['role' => 'admin']);
        $workspaceRussel->users()->attach($marcus->id, ['role' => 'admin']);
        $workspaceRussel->users()->attach($alex->id, ['role' => 'member']);
        $workspaceRussel->users()->attach($jessica->id, ['role' => 'member']);
        $workspaceRussel->users()->attach($sarah->id, ['role' => 'viewer']);

        // 3. Create workspace-level labels for MCBERNARD CAMP
        $labelsCamp = [
            'Bug' => '#ef4444',
            'Feature' => '#3b82f6',
            'Refactor' => '#8b5cf6',
            'Marketing' => '#10b981',
            'UI/UX' => '#ec4899',
            'Security' => '#f59e0b',
        ];

        $campLabels = [];
        foreach ($labelsCamp as $name => $color) {
            $campLabels[$name] = Label::create([
                'workspace_id' => $workspaceCamp->id,
                'name' => $name,
                'color' => $color,
            ]);
        }

        // Create workspace-level labels for Vance Russel LLC
        $labelsRussel = [
            'Design' => '#ec4899',
            'Backend' => '#8b5cf6',
            'Frontend' => '#3b82f6',
            'Compliance' => '#f59e0b',
            'Client Approved' => '#10b981',
        ];

        $russelLabels = [];
        foreach ($labelsRussel as $name => $color) {
            $russelLabels[$name] = Label::create([
                'workspace_id' => $workspaceRussel->id,
                'name' => $name,
                'color' => $color,
            ]);
        }

        // 4. Create projects in MCBERNARD CAMP
        $projectRedesign = Project::create([
            'workspace_id' => $workspaceCamp->id,
            'name' => 'Website Redesign',
            'description' => 'Redesign the focusflow.app homepage and app dashboard for a premium, high-converting look and feel.',
        ]);

        $projectMobile = Project::create([
            'workspace_id' => $workspaceCamp->id,
            'name' => 'Mobile App Development',
            'description' => 'Build the iOS and Android companion apps using React Native and Inertia webviews.',
        ]);

        $projectCoreApi = Project::create([
            'workspace_id' => $workspaceCamp->id,
            'name' => 'SaaS Core APIs',
            'description' => 'Refactor core authentication, stripe webhook handling, and real-time subscription logic.',
        ]);

        // 5. Create projects in Vance Russel LLC
        $projectBranding = Project::create([
            'workspace_id' => $workspaceRussel->id,
            'name' => 'Branding & Style Guide',
            'description' => 'Develop brand manual, typography, logo variations, and color schemes.',
        ]);

        $projectEcom = Project::create([
            'workspace_id' => $workspaceRussel->id,
            'name' => 'E-Commerce Platform',
            'description' => 'Integrate WooCommerce/Laravel custom storefront for high-volume sales.',
        ]);

        // --- SEED TASKS FOR WEBSITE REDESIGN (MCBERNARD CAMP) ---

        // Task 1: Backlog - Optimize Postgres Queries
        $taskOpt = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectRedesign->id,
            'title' => 'Optimize Postgres Queries',
            'description' => "Use EXPLAIN to analyze slow loads on kanban queries and add compound database indexes.\n\nCurrently, fetching workspaces with tasks takes over 1.2s under mock load. Need to optimize this query.",
            'status' => TaskStatus::Backlog,
            'priority' => TaskPriority::Medium,
        ]);
        $taskOpt->assignees()->attach([$alex->id, $primaryUser->id]);
        $taskOpt->labels()->attach([$campLabels['Refactor']->id]);

        // Task 2: Backlog - Integrate Horizon Dashboard
        $taskHor = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectRedesign->id,
            'title' => 'Integrate Horizon Dashboard',
            'description' => "Configure Redis queue monitor, configure thresholds for slack alerts on failed jobs.\n\nWe need to track the processing times of outbound notifications.",
            'status' => TaskStatus::Backlog,
            'priority' => TaskPriority::Low,
        ]);
        $taskHor->assignees()->attach([$alex->id]);
        $taskHor->labels()->attach([$campLabels['Security']->id]);

        // Task 3: In Progress - Fix Dark Mode Hover colors
        $taskHover = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectRedesign->id,
            'title' => 'Fix Dark Mode Hover fullbright colors',
            'description' => "The dark mode hover color is currently going fullbright when selecting elements on the Task Modal. Let's standardize the hover tailwind classes using our semantic bg-surface-2 and bg-surface-3 values.",
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::High,
        ]);
        $taskHover->assignees()->attach([$jessica->id]);
        $taskHover->labels()->attach([$campLabels['Bug']->id, $campLabels['UI/UX']->id]);

        Comment::create([
            'task_id' => $taskHover->id,
            'user_id' => $jessica->id,
            'content' => "I tracked this down to a conflicting custom border hover rule in task-modal's local styles. Resetting it to match the rest of the board's aesthetics.",
        ]);

        // Task 4: In Progress - Stripe Webhook Handlers
        $taskStripe = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectRedesign->id,
            'title' => 'Stripe Webhook Handlers',
            'description' => "Write robust integration tests using Pest PHP for cashier webhook triggers on subscription lifecycle events (payment failures, upgrades, cancellations).",
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::High,
        ]);
        $taskStripe->assignees()->attach([$primaryUser->id]);
        $taskStripe->labels()->attach([$campLabels['Feature']->id]);

        Comment::create([
            'task_id' => $taskStripe->id,
            'user_id' => $primaryUser->id,
            'content' => "Local stripe CLI webhook forwarding is configured. Beginning setup of subscription event tests.",
        ]);

        // Task 5: In Review - Centered Task Modal design
        $taskModal = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectRedesign->id,
            'title' => 'Centered Task Modal design',
            'description' => "Replace the old right drawer with a premium centered modal featuring backdrop blur and scale-in animations.\n\nMake sure that the close buttons are highly visible and accessibility tags (tabindex) prevent auto-focusing on the title input.",
            'status' => TaskStatus::InReview,
            'priority' => TaskPriority::High,
        ]);
        $taskModal->assignees()->attach([$jessica->id, $primaryUser->id]);
        $taskModal->labels()->attach([$campLabels['UI/UX']->id, $campLabels['Feature']->id]);

        Comment::create([
            'task_id' => $taskModal->id,
            'user_id' => $jessica->id,
            'content' => "The centered modal layout is complete. Let me know what you think of the scale-in micro-animation!",
        ]);
        Comment::create([
            'task_id' => $taskModal->id,
            'user_id' => $primaryUser->id,
            'content' => "Absolutely brilliant work! Verified that the dark mode hover states don't go fullbright anymore and the Close button contrast looks solid.",
        ]);
        Comment::create([
            'task_id' => $taskModal->id,
            'user_id' => $sarah->id,
            'content' => "Tested on mobile too, looks responsive and smooth. Let's merge this to main.",
        ]);

        // Task 6: Done - Vite and Tailwind v4 setup
        $taskVite = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectRedesign->id,
            'title' => 'Vite and Tailwind v4 setup',
            'description' => "Initialize Tailwind CSS v4 using the new Vite plugin framework and configure reference directives inside scoped Vue styles.",
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::Medium,
        ]);
        $taskVite->assignees()->attach([$alex->id]);
        $taskVite->labels()->attach([$campLabels['Feature']->id, $campLabels['UI/UX']->id]);

        // Task 7: Done - Multi-tenant Database Routing
        $taskTenant = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectRedesign->id,
            'title' => 'Multi-tenant Database Routing',
            'description' => "Setup workspace-scoped route switching and redirect scoping based on previous paths so that active context transitions smoothly.",
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::High,
        ]);
        $taskTenant->assignees()->attach([$primaryUser->id]);
        $taskTenant->labels()->attach([$campLabels['Feature']->id, $campLabels['Security']->id]);


        // --- SEED TASKS FOR MOBILE APP DEVELOPMENT (MCBERNARD CAMP) ---
        $taskPush = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectMobile->id,
            'title' => 'Setup Push Notification Channels',
            'description' => "Integrate Expo Push Notification API endpoints into our Laravel background job dispatchers.",
            'status' => TaskStatus::Backlog,
            'priority' => TaskPriority::High,
        ]);
        $taskPush->assignees()->attach([$alex->id]);
        $taskPush->labels()->attach([$campLabels['Feature']->id]);

        $taskAuth = Task::create([
            'workspace_id' => $workspaceCamp->id,
            'project_id' => $projectMobile->id,
            'title' => 'Mobile Login Screen UI',
            'description' => "Design clean, high-fidelity auth pages for Mobile with biometric login (FaceID/TouchID) integrations.",
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::Medium,
        ]);
        $taskAuth->assignees()->attach([$jessica->id]);
        $taskAuth->labels()->attach([$campLabels['UI/UX']->id]);

        Comment::create([
            'task_id' => $taskAuth->id,
            'user_id' => $jessica->id,
            'content' => "Finished Figma designs. Beginning work on Expo router views.",
        ]);


        // --- SEED TASKS FOR BRANDING (VANCE RUSSEL LLC) ---
        $taskLogo = Task::create([
            'workspace_id' => $workspaceRussel->id,
            'project_id' => $projectBranding->id,
            'title' => 'Logo Variations & Export',
            'description' => "Create and package Vance Russel LLC logo in SVG, PNG, and EPS formats (Dark theme, Light theme, and monochrome).",
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::Low,
        ]);
        $taskLogo->assignees()->attach([$jessica->id]);
        $taskLogo->labels()->attach([$russelLabels['Design']->id, $russelLabels['Client Approved']->id]);

        $taskGuide = Task::create([
            'workspace_id' => $workspaceRussel->id,
            'project_id' => $projectBranding->id,
            'title' => 'Brand Guideline Handbook',
            'description' => "Draft the usage guide, typography spacing, logo exclusion zones, and brand voice guidelines.",
            'status' => TaskStatus::InReview,
            'priority' => TaskPriority::Medium,
        ]);
        $taskGuide->assignees()->attach([$marcus->id, $jessica->id]);
        $taskGuide->labels()->attach([$russelLabels['Design']->id]);

        Comment::create([
            'task_id' => $taskGuide->id,
            'user_id' => $marcus->id,
            'content' => "Draft has been uploaded to Drive. Jessica, please verify the typography spec before we send it to the client.",
        ]);
    }
}
