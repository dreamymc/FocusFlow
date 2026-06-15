PHPUnit 12.5.29 by Sebastian Bergmann and contributors.
Runtime: PHP 8.5.4 with PCOV 1.0.12
Configuration: /home/visionmc/projects/focusflow/phpunit.xml
Time: 00:13.560, Memory: 84.50 MB
/home/visionmc/projects/focusflow/tests/Feature/AppShellTest.php
 unauthenticated users are redirected to login
 authenticated users are redirected to dashboard from root
 authenticated users can render dashboard page with shared properties
/home/visionmc/projects/focusflow/tests/Feature/Auth/LoginTest.php
 it logs in a user successfully
 it fails to log in with incorrect password
/home/visionmc/projects/focusflow/tests/Feature/Auth/LogoutTest.php
 it logs out a user successfully
 it prevents unauthenticated user from logging out
/home/visionmc/projects/focusflow/tests/Feature/Auth/RegisterTest.php
 it registers a user successfully
 it requires name, email, and password to register
 it requires a valid email address to register
 it requires password to be at least 8 characters
 it cannot register with an already existing email
/home/visionmc/projects/focusflow/tests/Feature/Auth/WebAuthTest.php
 it renders the login screen
 it renders the register screen
 it logs in a user via session successfully
 it fails web login with validation errors
 it registers a user via session successfully
 it fails web registration if passwords do not match
 it logs out a web session successfully
/home/visionmc/projects/focusflow/tests/Feature/AuthorizationTest.php
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Admin,·'admin'),·'create_project',·201)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Member,·'member'),·'create_project',·201)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Viewer,·'viewer'),·'create_project',·403)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Admin,·'admin'),·'update_project',·200)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Member,·'member'),·'update_project',·200)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Viewer,·'viewer'),·'update_project',·403)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Admin,·'admin'),·'delete_project',·204)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Member,·'member'),·'delete_project',·204)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Viewer,·'viewer'),·'delete_project',·403)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Admin,·'admin'),·'create_task',·201)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Member,·'member'),·'create_task',·201)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Viewer,·'viewer'),·'create_task',·403)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Admin,·'admin'),·'update_task',·200)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Member,·'member'),·'update_task',·200)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Viewer,·'viewer'),·'update_task',·403)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Admin,·'admin'),·'delete_task',·204)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Member,·'member'),·'delete_task',·204)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Viewer,·'viewer'),·'delete_task',·403)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Admin,·'admin'),·'move_task',·200)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Member,·'member'),·'move_task',·200)
 it enforces role authorization matrix with (App\Enums\WorkspaceRole·Enum·(Viewer,·'viewer'),·'move_task',·403)
/home/visionmc/projects/focusflow/tests/Feature/Billing/BillingPortalTest.php
 it returns a billing portal url
/home/visionmc/projects/focusflow/tests/Feature/Billing/StripeWebhookTest.php
 it activates pro plan on successful payment
 it handles customer subscription deleted
/home/visionmc/projects/focusflow/tests/Feature/Billing/WorkspaceProGateTest.php
 it blocks member invites beyond 3 on the free plan
 it allows member invites beyond 3 on the pro plan
/home/visionmc/projects/focusflow/tests/Feature/ErrorPageTest.php
 it renders custom error page for 404 when debug is disabled
/home/visionmc/projects/focusflow/tests/Feature/Integrations/ChannelAuthTest.php
 it authorizes workspace members for workspace channel
 it authorizes workspace members for task presence channel
/home/visionmc/projects/focusflow/tests/Feature/Integrations/ScheduleTest.php
 it schedules the weekly digest every Monday
/home/visionmc/projects/focusflow/tests/Feature/Integrations/TaskCompletedSlackNotificationTest.php
 it registers SendSlackNotification listener for TaskCompleted event
 it sends slack notification when listener handles event
/home/visionmc/projects/focusflow/tests/Feature/Integrations/WeeklyDigestMailTest.php
 it queues the weekly digest mail when command is executed
/home/visionmc/projects/focusflow/tests/Feature/ProjectTest.php
 it allows admin and member to create a project, but denies viewer
 it validates project creation name is required
 it lists projects for workspace members, but denies non-members
 it shows a project to workspace members, but denies non-members
 it allows admin and member to update project, but denies viewer
 it allows admin and member to delete project, but denies viewer
/home/visionmc/projects/focusflow/tests/Feature/ProjectWebTest.php
 it renders projects index page for workspace members
 it allows admins and members to create a project
 it denies viewers from creating projects
/home/visionmc/projects/focusflow/tests/Feature/TaskTest.php
 it allows admin and member to create a task, but denies viewer
 it validates task title is required
 it lists and filters tasks in a project
 it shows a task to workspace members
 it allows admin and member to update a task, but denies viewer
 it allows admin and member to delete a task, but denies viewer
 it allows workspace member to move a task status, but denies viewer
/home/visionmc/projects/focusflow/tests/Feature/Workspace/AcceptInviteTest.php
 it accepts an invite successfully
 it rejects invalid invite tokens
 it requires authenticated user to have matching email
/home/visionmc/projects/focusflow/tests/Feature/Workspace/BillingWebTest.php
 it allows workspace admins to view the billing page
 it returns 403 when non-admins try to view the billing page
 it redirects workspace admin to stripe billing portal on post
/home/visionmc/projects/focusflow/tests/Feature/Workspace/CreateWorkspaceTest.php
 it creates a workspace successfully
 it fails to create workspace with missing name
 it requires authentication to create workspace
/home/visionmc/projects/focusflow/tests/Feature/Workspace/InviteMemberTest.php
 it invites a member to a workspace successfully
 it prevents non-admins from inviting members
 it validates invite data
/home/visionmc/projects/focusflow/tests/Feature/Workspace/KanbanWebTest.php
 it renders kanban board for workspace members
 it groups tasks by their correct status
 it denies viewers from moving tasks
/home/visionmc/projects/focusflow/tests/Feature/Workspace/SwitchWorkspaceTest.php
 it requires authentication to switch workspace
 it validates workspace switch request input
 it prevents user from switching to a workspace they are not a member of
 it switches the workspace successfully, sets the session, and redirects back
/home/visionmc/projects/focusflow/tests/Feature/Workspace/WorkspaceWebTest.php
 it renders workspace creation page
 it creates a workspace via web form and redirects to dashboard
 it allows admins to view settings
 it denies members and viewers from settings
 it allows admins to update workspace name
 it allows admins to invite members via web form
/home/visionmc/projects/focusflow/tests/Unit/Actions/AcceptInviteActionTest.php
 it accepts an invitation and adds user to workspace
/home/visionmc/projects/focusflow/tests/Unit/Actions/CancelSubscriptionActionTest.php
 it cancels a subscription
/home/visionmc/projects/focusflow/tests/Unit/Actions/CreateProjectActionTest.php
 it creates a project
/home/visionmc/projects/focusflow/tests/Unit/Actions/CreateTaskActionTest.php
 it creates a task with correct data
 it creates a task with default status and priority
/home/visionmc/projects/focusflow/tests/Unit/Actions/CreateWorkspaceActionTest.php
 it creates a workspace and assigns user as admin
/home/visionmc/projects/focusflow/tests/Unit/Actions/DeleteProjectActionTest.php
 it deletes a project
/home/visionmc/projects/focusflow/tests/Unit/Actions/DeleteTaskActionTest.php
 it deletes a task
/home/visionmc/projects/focusflow/tests/Unit/Actions/InviteMemberActionTest.php
 it invites a member
/home/visionmc/projects/focusflow/tests/Unit/Actions/MoveTaskActionTest.php
 it moves a task
 it dispatches TaskCompleted when moved to Done
/home/visionmc/projects/focusflow/tests/Unit/Actions/RegisterActionTest.php
 it registers a user
/home/visionmc/projects/focusflow/tests/Unit/Actions/SubscribeWorkspaceActionTest.php
 it subscribes a workspace
/home/visionmc/projects/focusflow/tests/Unit/Actions/UpdateProjectActionTest.php
 it updates a project
/home/visionmc/projects/focusflow/tests/Unit/Actions/UpdateTaskActionTest.php
 it updates a task
/home/visionmc/projects/focusflow/tests/Unit/Billing/CancelSubscriptionActionTest.php
 it cancels a workspace subscription
/home/visionmc/projects/focusflow/tests/Unit/Billing/PlanTest.php
 it has free and pro tiers with feature flags
/home/visionmc/projects/focusflow/tests/Unit/Billing/SubscribeWorkspaceActionTest.php
 it subscribes a workspace to a plan
/home/visionmc/projects/focusflow/tests/Unit/Billing/WorkspaceBillableTest.php
 it uses the billable trait
/home/visionmc/projects/focusflow/tests/Unit/ExampleTest.php
 that true is true
/home/visionmc/projects/focusflow/tests/Unit/Integrations/SlackNotificationServiceTest.php
 it sends a webhook to slack
/home/visionmc/projects/focusflow/tests/Unit/Services/SlackNotificationServiceTest.php
 it sends a slack notification
OK (115 tests, 381 assertions)
Generating code coverage report in PHP format .. done [00:00.002]
{"tool":"pest","result":"passed","tests":115,"passed":115,"assertions":381,"duration_ms":13556}
 Actions/AcceptInviteAction .. 100.0% 
 Actions/CancelSubscriptionAction .. 100.0% 
 Actions/CreateProjectAction .. 100.0% 
 Actions/CreateTaskAction .. 100.0% 
 Actions/CreateWorkspaceAction .. 100.0% 
 Actions/DeleteProjectAction .. 100.0% 
 Actions/DeleteTaskAction .. 100.0% 
 Actions/InviteMemberAction .. 100.0% 
 Actions/LoginAction .. 18..19 / 84.6% 
 Actions/MoveTaskAction .. 100.0% 
 Actions/RegisterAction .. 100.0% 
 Actions/SubscribeWorkspaceAction .. 100.0% 
 Actions/UpdateProjectAction .. 100.0% 
 Actions/UpdateTaskAction .. 23 / 90.9% 
 Actions/UpdateWorkspaceAction .. 100.0% 
 Enums/InviteStatus .. 0.0% 
 Enums/TaskPriority .. 100.0% 
 Enums/TaskStatus .. 100.0% 
 Enums/WorkspaceRole .. 0.0% 
 Events/TaskAssigned .. 0.0% 
 Events/TaskCommented .. 0.0% 
 Events/TaskCompleted .. 100.0% 
 Events/TaskMoved .. 100.0% 
 Http/Controllers/Api/V1/Auth/LoginController .. 100.0% 
 Http/Controllers/Api/V1/Auth/LogoutController .. 100.0% 
 Http/Controllers/Api/V1/Auth/RegisterController .. 100.0% 
 Http/Controllers/Api/V1/InvitationController .. 100.0% 
 Http/Controllers/Api/V1/ProjectController .. 47, 61 / 90.5% 
 Http/Controllers/Api/V1/TaskController .. 26, 47, 61, 70, 84, 99 / 86.4% 
 Http/Controllers/Api/V1/WorkspaceController .. 17, 32, 38 / 78.6% 
 Http/Controllers/Controller .. 100.0% 
 Http/Controllers/Web/Auth/LoginController .. 48..51 / 78.9% 
 Http/Controllers/Web/Auth/LogoutController .. 100.0% 
 Http/Controllers/Web/Auth/RegisterController .. 100.0% 
 Http/Controllers/Web/BillingController .. 39 / 95.0% 
 Http/Controllers/Web/DashboardController .. 23, 33..34, 35..36 / 88.9% 
 Http/Controllers/Web/KanbanController .. 17 / 95.2% 
 Http/Controllers/Web/ProjectController .. 20..23, 19..24 / 68.4% 
 Http/Controllers/Web/WorkspaceController .. 59, 74 / 95.2% 
 Http/Controllers/Web/WorkspaceSwitchController .. 100.0% 
 Http/Controllers/Webhooks/StripeController .. 100.0% 
 Http/Middleware/HandleInertiaRequests .. 100.0% 
 Http/Middleware/SetSecurityHeaders .. 100.0% 
 Http/Middleware/WorkspaceScope .. 17, 21 / 83.3% 
 Http/Requests/AcceptInvitationRequest .. 100.0% 
 Http/Requests/Auth/LoginRequest .. 100.0% 
 Http/Requests/Auth/RegisterRequest .. 100.0% 
 Http/Requests/ShowWorkspaceRequest .. 0.0% 
 Http/Requests/StoreInvitationRequest .. 100.0% 
 Http/Requests/StoreProjectRequest .. 100.0% 
 Http/Requests/StoreTaskRequest .. 100.0% 
 Http/Requests/StoreWorkspaceRequest .. 100.0% 
 Http/Requests/UpdateTaskRequest .. 29..39 / 75.0% 
 Http/Resources/LabelResource .. 100.0% 
 Http/Resources/ProjectResource .. 100.0% 
 Http/Resources/TaskCollection .. 100.0% 
 Http/Resources/TaskResource .. 100.0% 
 Http/Resources/UserResource .. 100.0% 
 Http/Resources/WorkspaceResource .. 100.0% 
 Listeners/SendSlackNotification .. 100.0% 
 Mail/WeeklyDigestMail .. 30..37, 32 / 50.0% 
 Models/Invitation .. 35 / 50.0% 
 Models/Label .. 0.0% 
 Models/Plan .. 100.0% 
 Models/Project .. 26 / 75.0% 
 Models/Task .. 44 / 92.9% 
 Models/User .. 100.0% 
 Models/Workspace .. 100.0% 
 Policies/ProjectPolicy .. 13..18 / 71.4% 
 Policies/TaskPolicy .. 13..18 / 71.4% 
 Providers/AppServiceProvider .. 29 / 93.3% 
 Providers/HorizonServiceProvider .. 31..32 / 50.0% 
 Services/SlackNotificationService .. 100.0% 
 
 Total: 89.0 % 
