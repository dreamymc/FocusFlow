# FocusFlow Frontend — Complete UI Implementation Plan
> Master agentic plan for antigravity CLI · 9 phases from zero to deployed SaaS

---

## Stack Decisions

| Concern | Decision | Rationale |
|---|---|---|
| SPA Bridge | **Inertia.js** | Eliminates token auth complexity on the frontend; Laravel sessions handle auth; zero duplication of API endpoints |
| Component Library | **shadcn-vue** | Headless + Tailwind-native; copy-paste ownership shows craft; not a black-box dependency |
| Drag & Drop | **vue-draggable-plus** | Lightweight Vue 3 DnD backed by Sortable.js; perfect for kanban |
| Utilities | **@vueuse/core** | Composables for debounce, media queries, click-outside |
| State | **Inertia shared props + Vue reactive()** | No Pinia for MVP — simpler architecture that's easier to explain in an interview |
| Auth Strategy | **New session-based web routes** + **existing Sanctum API untouched** | Clean separation: Inertia app uses session cookies; external API consumers keep using tokens |

---

## Design System

### Colors
```
PRIMARY (Indigo)         #6366F1  ← Matches the logo
PRIMARY DARK             #4F46E5
PRIMARY LIGHT            #EEF2FF

SECONDARY (Violet)       #8B5CF6
SECONDARY DARK           #7C3AED
SECONDARY LIGHT          #F5F3FF

SURFACE                  #FFFFFF
SURFACE 2 (page bg)      #F8FAFC
SURFACE 3 (kanban bg)    #F1F5F9
SIDEBAR                  #FAFBFF  ← barely-there blue-white

TEXT PRIMARY             #0F172A
TEXT SECONDARY           #475569
TEXT MUTED               #94A3B8

BORDER                   #E2E8F0
BORDER STRONG            #CBD5E1

ACCENT COLORS (Notion-style labels/icons):
  Red    #EF4444   Orange #F97316   Yellow #F59E0B   Green  #10B981
  Blue   #3B82F6   Purple #8B5CF6   Pink   #EC4899   Gray   #6B7280

KANBAN STATUS COLORS:
  Backlog     #6B7280  (gray)
  In Progress #3B82F6  (blue)
  In Review   #F59E0B  (amber)
  Done        #10B981  (emerald)
```

### Typography
```
DISPLAY   Plus Jakarta Sans  700/800  — page titles, workspace names, logo
UI        Inter              300–600  — everything else: labels, body, buttons
MONO      JetBrains Mono     400/500  — task IDs, timestamps, code
```

### Signature Element
**Colored workspace icons** — every workspace and project gets one of the 8 accent colors rendered as a rounded square with a bold initial. These appear in the sidebar, project cards, kanban header, and task cards. This makes the UI feel alive and personal even with no real photos. It is the single most "Notion-like" element and gives the UI its identity.

### UI Rules
- Cards: `rounded-lg border border-border bg-surface shadow-sm`
- Inputs: `rounded-md border border-border`
- Badges: `rounded-full`
- Avatars: `rounded-full`
- Sidebar width: `256px` (fixed)
- Page content padding: `px-6 py-8`

---

## Architecture Overview

```
Auth flow:
  Browser → POST /login (session) → Inertia app loads
  [Existing API tokens at /api/v1/* are untouched]

Data flow:
  Inertia Controller → Inertia::render('Page', $data) → Vue page receives $data as props
  Form submissions → router.post('/route', data) → controller → redirect with flash

Real-time:
  Laravel Echo (echo.js) → Reverb → Vue components listen for events
  Events update local reactive state without page reload

RBAC:
  HandleInertiaRequests shares user + currentWorkspace + userRole
  usePermissions.js composable provides can('action') helper
  Components use v-if="can('manage-billing')" etc.
```

---

## Full File Tree (what gets built)

```
app/Http/
├── Controllers/Web/
│   ├── Auth/
│   │   ├── LoginController.php
│   │   ├── RegisterController.php
│   │   └── LogoutController.php
│   ├── DashboardController.php
│   ├── WorkspaceController.php
│   ├── ProjectController.php
│   ├── KanbanController.php
│   └── BillingController.php
└── Middleware/
    └── HandleInertiaRequests.php  ← shares auth, role, flash, workspaces

resources/
├── css/app.css                    ← Tailwind + CSS variables
├── views/app.blade.php            ← Inertia root (replaces welcome.blade.php)
└── js/
    ├── app.js                     ← Inertia entry point (replaces current)
    ├── echo.js                    ← UNTOUCHED
    ├── Composables/
    │   ├── useTaskUpdates.js      ← UNTOUCHED (presence channel)
    │   └── usePermissions.js      ← NEW (can('action') helper)
    ├── Layouts/
    │   ├── AuthenticatedLayout.vue
    │   └── GuestLayout.vue
    ├── Pages/
    │   ├── Auth/
    │   │   ├── Login.vue
    │   │   └── Register.vue
    │   ├── Dashboard.vue
    │   ├── Workspaces/
    │   │   ├── Create.vue
    │   │   └── Settings.vue
    │   ├── Projects/
    │   │   ├── Index.vue
    │   │   └── Kanban.vue
    │   └── Billing/
    │       └── Index.vue
    └── Components/
        ├── NotificationBell.vue   ← UNTOUCHED (existing)
        ├── AppSidebar.vue
        ├── AppNavbar.vue
        ├── WorkspaceSwitcher.vue
        ├── ColorIcon.vue          ← Notion-style colored initial icon
        ├── KanbanBoard.vue
        ├── KanbanColumn.vue
        ├── TaskCard.vue
        ├── TaskModal.vue          ← shadcn Sheet (slide-over)
        ├── PresenceAvatars.vue    ← uses useTaskUpdates.js
        ├── InviteMemberModal.vue
        ├── MemberList.vue
        └── ui/                    ← shadcn-vue auto-generated components

DESIGN_SYSTEM.md                   ← agent reference file (created Phase 0)
```

---
---

# PHASE 0 — Foundation
**Goal:** Install Inertia.js, shadcn-vue, configure the design system, replace the Inertia entry point.

## Antigravity Prompt — Phase 0

```
You are implementing the frontend for FocusFlow, a Laravel 11 + Vue 3 SaaS. The backend is 
complete. Phase 0 is your job: install the stack and configure the foundation.

STEP 1 — READ THESE FILES FIRST:
  package.json
  vite.config.js
  composer.json
  resources/js/app.js
  resources/views/welcome.blade.php
  routes/web.php
  bootstrap/app.php

STEP 2 — INSTALL BACKEND PACKAGES:
  Run: composer require inertiajs/inertia-laravel
  Run: php artisan inertia:middleware
  
  Then open bootstrap/app.php. In the ->withMiddleware() closure, register 
  HandleInertiaRequests in the web middleware group. It must come LAST, after 
  SubstituteBindings. Add this inside the closure:
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
  Add import at top: use App\Http\Middleware\HandleInertiaRequests;

STEP 3 — INSTALL FRONTEND PACKAGES:
  Run: npm install @inertiajs/vue3 @vueuse/core vue-draggable-plus

STEP 4 — INSTALL SHADCN-VUE:
  Run: npx shadcn-vue@latest init
  Answer the prompts: TypeScript=No, style=default, base color=slate, 
  CSS variables=yes, tailwind config=tailwind.config.js, 
  components alias=@/Components/ui, utils alias=@/lib/utils

  Then install all components we'll need:
  Run: npx shadcn-vue@latest add button input label textarea badge avatar 
       card dialog sheet dropdown-menu separator tooltip toast sonner

STEP 5 — CREATE THE INERTIA ROOT BLADE TEMPLATE:
  Create resources/views/app.blade.php:
  
  <!DOCTYPE html>
  <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
  <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      @inertiaHead
  </head>
  <body class="h-full antialiased">
      @inertia
  </body>
  </html>

STEP 6 — REWRITE resources/js/app.js:
  Replace the entire file with:

  import './echo';
  import { createApp, h } from 'vue';
  import { createInertiaApp } from '@inertiajs/vue3';
  import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

  createInertiaApp({
      title: (title) => title ? `${title} — FocusFlow` : 'FocusFlow',
      resolve: (name) =>
          resolvePageComponent(
              `./Pages/${name}.vue`,
              import.meta.glob('./Pages/**/*.vue'),
          ),
      setup({ el, App, props, plugin }) {
          return createApp({ render: () => h(App, props) })
              .use(plugin)
              .mount(el);
      },
      progress: { color: '#6366F1' },
  });

STEP 7 — UPDATE tailwind.config.js WITH THE DESIGN SYSTEM:
  Extend the theme with these exact custom colors and fonts:

  colors: {
    primary: { DEFAULT: '#6366F1', dark: '#4F46E5', light: '#EEF2FF', text: '#FFFFFF' },
    secondary: { DEFAULT: '#8B5CF6', dark: '#7C3AED', light: '#F5F3FF' },
    surface: { DEFAULT: '#FFFFFF', 2: '#F8FAFC', 3: '#F1F5F9', sidebar: '#FAFBFF' },
    text: { DEFAULT: '#0F172A', secondary: '#475569', muted: '#94A3B8' },
    border: { DEFAULT: '#E2E8F0', strong: '#CBD5E1' },
    accent: {
      red: '#EF4444', orange: '#F97316', yellow: '#F59E0B', green: '#10B981',
      blue: '#3B82F6', purple: '#8B5CF6', pink: '#EC4899', gray: '#6B7280',
    },
    status: {
      backlog: '#6B7280', 'in-progress': '#3B82F6', review: '#F59E0B', done: '#10B981',
    },
  },
  fontFamily: {
    sans: ['Inter', 'ui-sans-serif', 'system-ui'],
    display: ['Plus Jakarta Sans', 'Inter', 'ui-sans-serif'],
    mono: ['JetBrains Mono', 'ui-monospace'],
  },

STEP 8 — REWRITE resources/css/app.css:
  @tailwind base;
  @tailwind components;
  @tailwind utilities;

  :root {
    --ff-primary: #6366F1;
    --ff-primary-dark: #4F46E5;
    --ff-secondary: #8B5CF6;
    --ff-sidebar-width: 256px;
  }

  @layer base {
    body { @apply bg-surface-2 text-text font-sans; }
    h1, h2, h3 { @apply font-display; }
  }

STEP 9 — UPDATE HandleInertiaRequests.php:
  Open app/Http/Middleware/HandleInertiaRequests.php.
  Update the share() method to return:

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
  ]);

STEP 10 — UPDATE routes/web.php:
  Replace the content with:

  <?php
  use Illuminate\Support\Facades\Route;
  use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;
  use App\Http\Controllers\Webhooks\StripeController;

  // Stripe webhook — must be BEFORE auth middleware
  Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook'])
      ->middleware(VerifyWebhookSignature::class);

  // Auth routes (Phase 1)
  // App routes (Phase 2+)

STEP 11 — CREATE DESIGN_SYSTEM.md IN THE PROJECT ROOT:
  Write a reference file documenting: all color tokens with hex values, font 
  families and usage rules, the 8 accent colors for workspace icons, kanban 
  status colors, UI rules (border-radius, shadow, border tokens). Future phases 
  will reference this file.

ACCEPTANCE CRITERIA:
  - composer install and npm install complete with no errors
  - resources/views/app.blade.php exists with @inertia directive
  - resources/js/app.js uses createInertiaApp
  - tailwind.config.js contains all custom color and font tokens
  - DESIGN_SYSTEM.md exists in project root
  - Running: php artisan serve & npm run dev starts both servers without errors
  - Note: visiting localhost:8000 will show a blank page or 404 — that's fine, 
    there are no pages yet. The goal is zero errors on startup.
```

---

# PHASE 1 — Authentication
**Goal:** Build Login and Register pages. Wire session-based auth for the web frontend (separate from the existing Sanctum token API).

## Antigravity Prompt — Phase 1

```
You are building Phase 1 of the FocusFlow frontend: Authentication screens.

Context: FocusFlow has existing Sanctum token auth at /api/v1/login and /api/v1/register. 
Those routes are untouched. For the Inertia web app, we add SEPARATE web routes that use 
standard Laravel session auth (Auth::attempt). This is the clean Inertia pattern.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  app/Models/User.php
  routes/web.php
  app/Http/Middleware/HandleInertiaRequests.php
  resources/js/app.js

STEP 2 — CREATE WEB AUTH CONTROLLERS:

  Create app/Http/Controllers/Web/Auth/LoginController.php:
    - create() method: returns Inertia::render('Auth/Login')
    - store() method: validates email+password, calls Auth::attempt(), 
      on success redirect to /dashboard, on fail return back with error

  Create app/Http/Controllers/Web/Auth/RegisterController.php:
    - create() method: returns Inertia::render('Auth/Register')  
    - store() method: validates name+email+password+password_confirmation,
      creates User, calls Auth::login(), redirects to /dashboard

  Create app/Http/Controllers/Web/Auth/LogoutController.php:
    - destroy() method: calls Auth::logout(), invalidates session, 
      regenerates token, redirects to /login

STEP 3 — ADD AUTH ROUTES TO routes/web.php:
  Under the guest middleware group:
    GET  /login    → LoginController@create   (name: login)
    POST /login    → LoginController@store
    GET  /register → RegisterController@create (name: register)
    POST /register → RegisterController@store

  Under the auth middleware group:
    POST /logout   → LogoutController@destroy  (name: logout)

STEP 4 — CREATE GuestLayout.vue:
  File: resources/js/Layouts/GuestLayout.vue
  
  Design: Full-screen split layout. Left half (hidden on mobile) = brand panel 
  with the FocusFlow logo, tagline "Where great teams ship.", and a subtle 
  geometric pattern background using primary (#6366F1) with lighter indigo shapes. 
  Right half = centered white card containing the slot (the form).
  
  Use font-display for the logo wordmark. The word "Focus" in white, "Flow" in 
  primary-light (#EEF2FF). Tagline in white/70 opacity.

STEP 5 — CREATE Login.vue:
  File: resources/js/Pages/Auth/Login.vue
  Layout: GuestLayout
  
  Form fields:
    - Email (type=email, label="Email address")
    - Password (type=password, label="Password")
    - Submit button: full-width, bg-primary, "Sign in to FocusFlow"
    - Link below form: "Don't have an account? Sign up" → /register
  
  Use useForm from @inertiajs/vue3 for the form. Show field-level errors 
  from $page.props.errors. Show flash.error if present.
  
  Design rules from DESIGN_SYSTEM.md:
    - Card: rounded-lg border border-border bg-surface shadow-sm p-8
    - Inputs: rounded-md border border-border, focus ring in primary color
    - Button: bg-primary text-white hover:bg-primary-dark rounded-md
    - Heading: font-display text-2xl font-bold text-text

STEP 6 — CREATE Register.vue:
  File: resources/js/Pages/Auth/Register.vue
  Layout: GuestLayout
  
  Form fields:
    - Name (text)
    - Email (email)
    - Password (password)
    - Confirm Password (password)
    - Submit button: "Create your account"
    - Link: "Already have an account? Sign in" → /login
  
  Same design rules as Login.vue.

ACCEPTANCE CRITERIA:
  - GET /login renders the Login page with the split layout
  - GET /register renders the Register page
  - Submitting valid credentials logs the user in and redirects to /dashboard 
    (404 on dashboard is fine — that's Phase 2)
  - Invalid credentials return to /login with an error message visible on screen
  - Registering a new user logs them in immediately
  - POST /logout clears the session and redirects to /login
  - The left brand panel looks polished (logo, tagline, colored background)
  - Mobile: the brand panel is hidden, the form takes full width
```

---

# PHASE 2 — App Shell
**Goal:** Build the authenticated layout (sidebar + navbar), shared Inertia data (user + role + workspaces), the permissions composable, and a basic Dashboard page.

## Antigravity Prompt — Phase 2

```
You are building Phase 2 of the FocusFlow frontend: the App Shell.

This is the most important structural phase. Everything else builds on top of this layout.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  app/Http/Middleware/HandleInertiaRequests.php
  app/Models/User.php
  app/Models/Workspace.php  (look for relationships and role methods)
  routes/web.php
  resources/js/Components/NotificationBell.vue  (existing — understand its props)

STEP 2 — UPDATE HandleInertiaRequests.php TO SHARE WORKSPACE + ROLE DATA:
  Update the share() method to also include:
  
  'workspaces' => fn () => $request->user() 
      ? $request->user()->workspaces()->select('id', 'name')->get()->map(fn($w) => [
          'id' => $w->id,
          'name' => $w->name,
          'slug' => $w->slug ?? $w->id,
        ])
      : [],
  
  'currentWorkspace' => fn () => session('current_workspace_id') 
      ? \App\Models\Workspace::find(session('current_workspace_id', 
          $request->user()?->workspaces()->first()?->id
        ))
      : $request->user()?->workspaces()->first(),
  
  'userRole' => fn () => ... (get the user's role in the current workspace — 
    check what method the Workspace model exposes for this, likely via 
    the workspace_user pivot or Spatie permissions)

STEP 3 — CREATE usePermissions.js COMPOSABLE:
  File: resources/js/Composables/usePermissions.js
  
  import { computed } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  
  export function usePermissions() {
    const page = usePage();
    const role = computed(() => page.props.userRole);
    
    const isAdmin  = computed(() => role.value === 'admin');
    const isMember = computed(() => ['admin', 'member'].includes(role.value));
    const isViewer = computed(() => role.value === 'viewer');
    
    const can = (action) => {
      const permissions = {
        'create-tasks':       isMember.value,
        'edit-tasks':         isMember.value,
        'move-tasks':         isMember.value,
        'delete-tasks':       isMember.value,
        'manage-projects':    isMember.value,
        'invite-members':     isAdmin.value,
        'manage-workspace':   isAdmin.value,
        'access-billing':     isAdmin.value,
        'manage-members':     isAdmin.value,
      };
      return permissions[action] ?? false;
    };
    
    return { role, isAdmin, isMember, isViewer, can };
  }

STEP 4 — CREATE ColorIcon.vue COMPONENT:
  File: resources/js/Components/ColorIcon.vue
  
  Props: name (string), color (string from: red/orange/yellow/green/blue/purple/pink/gray), 
         size (sm/md/lg, default md)
  
  Renders: A rounded square (rounded-lg) in the given accent color (using bg-accent-{color}) 
  with the first letter of `name` centered inside in white, bold, font-display.
  
  Sizes: sm=24px, md=32px, lg=40px
  
  This is the signature element of the FocusFlow UI. It should look polished.

STEP 5 — CREATE AppSidebar.vue:
  File: resources/js/Components/AppSidebar.vue
  
  Background: bg-surface-sidebar, border-r border-border, fixed left-0, full height
  Width: 256px (var(--ff-sidebar-width))
  
  Structure (top to bottom):
    1. LOGO AREA (top, ~60px): "FocusFlow" in font-display. "Focus" in text-primary, 
       "Flow" in text-secondary. Click → /dashboard.
    
    2. WORKSPACE SWITCHER: Shows current workspace ColorIcon + name. 
       Clicking opens a dropdown listing all workspaces (from $page.props.workspaces). 
       Switching workspace hits POST /workspaces/switch with {workspace_id}.
    
    3. NAV SECTION — PROJECTS (label: "Projects", small uppercase muted label):
       List of projects in the current workspace. Each item: ColorIcon (small) + 
       project name. Click → /workspaces/{id}/projects/{projectId}.
       + "New Project" button (Members+ only, using can('manage-projects')).
    
    4. NAV SECTION — WORKSPACE (label: "Workspace"):
       - "Members" link → /workspaces/{id}/settings#members
       - "Settings" link → /workspaces/{id}/settings (Admin only, v-if isAdmin)
       - "Billing" link → /billing (Admin only, v-if isAdmin)
    
    5. USER AREA (bottom, sticky): User avatar (initials in a circle) + name + 
       "Sign out" button that submits a POST form to /logout.
  
  Active state: bg-primary-light text-primary rounded-md for the current route.

STEP 6 — CREATE AppNavbar.vue:
  File: resources/js/Components/AppNavbar.vue
  
  A 56px tall top bar. Contents:
    Left: Current page title (passed as prop `title`)
    Right: NotificationBell component + User avatar dropdown
  
  Wire NotificationBell with :workspace-id="$page.props.currentWorkspace?.id"
  
  User dropdown (shadcn DropdownMenu): shows name, email, divider, "Sign out"

STEP 7 — CREATE AuthenticatedLayout.vue:
  File: resources/js/Layouts/AuthenticatedLayout.vue
  
  Props: title (string, optional)
  
  Structure:
    <div class="flex h-full">
      <AppSidebar />
      <div class="flex-1 flex flex-col ml-[256px]">
        <AppNavbar :title="title" />
        <main class="flex-1 overflow-auto p-6">
          <slot />
        </main>
      </div>
    </div>

STEP 8 — CREATE DashboardController.php:
  File: app/Http/Controllers/Web/DashboardController.php
  
  index() method: auth guard, returns Inertia::render('Dashboard', [
    'stats' => [
      'totalTasks'    => ... (count of tasks assigned to current user in current workspace),
      'activeTasks'   => ... (tasks in progress),
      'completedToday'=> ... (tasks marked done today),
    ],
  ])

STEP 9 — CREATE Dashboard.vue:
  File: resources/js/Pages/Dashboard.vue
  Layout: AuthenticatedLayout (title="Dashboard")
  
  Content: A 3-column stat card row at the top (Total Tasks, In Progress, Done Today),
  each card in bg-surface rounded-lg border border-border p-5 shadow-sm.
  Below: "Recent Activity" section showing the last 5 tasks assigned to the user 
  as a simple list with task name, project, and status badge.
  
  If the user has no workspace yet: show an empty state with a "Create your first 
  workspace" button → /workspaces/create.

STEP 10 — ADD ROUTES TO routes/web.php:
  Under auth middleware:
    GET /dashboard → DashboardController@index (name: dashboard)
    POST /workspaces/switch → WorkspaceSwitchController@store (create this tiny 
      controller that stores {workspace_id} in session and redirects back)
  
  Update the redirect after login to go to /dashboard.
  Add a root redirect: GET / → redirect to /dashboard if authed, else /login.

ACCEPTANCE CRITERIA:
  - Logging in redirects to /dashboard
  - /dashboard shows the 3-stat cards and recent activity
  - The sidebar is visible on all authenticated pages
  - The NotificationBell is in the navbar and receives the correct workspace ID
  - Switching workspaces updates the sidebar project list
  - "Billing" and "Settings" nav items are HIDDEN for Members and Viewers
  - Visiting /login while authenticated redirects to /dashboard
  - Visiting any auth route while logged out redirects to /login
```

---

# PHASE 3 — Workspaces
**Goal:** Workspace creation flow, workspace settings page (Admin), member management, invite modal.

## Antigravity Prompt — Phase 3

```
You are building Phase 3 of FocusFlow: Workspace management.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  app/Models/Workspace.php
  app/Http/Controllers/Api/V1/WorkspaceController.php   (study the existing logic)
  app/Http/Controllers/Api/V1/InvitationController.php  (study the invite logic)
  routes/web.php

STEP 2 — CREATE app/Http/Controllers/Web/WorkspaceController.php:
  Methods needed:
    - create(): Inertia::render('Workspaces/Create')
    - store(): validate {name}, create Workspace, attach creator as admin, 
      store in session as current_workspace_id, redirect to /dashboard with 
      flash success "Workspace created!"
    - settings($workspace): Admin only gate check. Inertia::render('Workspaces/Settings', 
      ['workspace' => $workspace, 'members' => $workspace->users()->withPivot('role')->get()])
    - update($workspace): Admin only. Validate + update name. Redirect back.
    - invite($workspace): Admin only. Delegate to the existing invitation logic 
      (reuse the service/action from the API layer if one exists, else replicate).
      Redirect back with flash success "Invitation sent to {email}".

STEP 3 — ADD ROUTES:
  Under auth middleware group:
    GET  /workspaces/create            → WorkspaceController@create    (name: workspaces.create)
    POST /workspaces                   → WorkspaceController@store     (name: workspaces.store)
    GET  /workspaces/{workspace}/settings → WorkspaceController@settings (name: workspaces.settings)
    PUT  /workspaces/{workspace}       → WorkspaceController@update
    POST /workspaces/{workspace}/invite → WorkspaceController@invite

STEP 4 — CREATE Workspaces/Create.vue:
  File: resources/js/Pages/Workspaces/Create.vue
  Layout: GuestLayout (not authenticated layout — user may have no workspace yet)
  
  A clean centered card with:
    - Heading: "Create your workspace" (font-display, text-2xl)
    - Subtext: "A workspace is where your team's projects and tasks live."
    - Input: Workspace name
    - Colorful preview: as the user types, show a ColorIcon with a random accent 
      color and the first letter, giving immediate visual feedback
    - Submit: "Create workspace →"

STEP 5 — CREATE MemberList.vue COMPONENT:
  File: resources/js/Components/MemberList.vue
  Props: members (array of {id, name, email, role, avatar?})
  
  Renders a table/list: avatar initials circle, name, email, role badge 
  (Admin=indigo, Member=violet, Viewer=gray), and a "Change role" dropdown 
  (Admin only) with options: Admin / Member / Viewer.

STEP 6 — CREATE InviteMemberModal.vue COMPONENT:
  File: resources/js/Components/InviteMemberModal.vue
  Uses shadcn Dialog. Triggered by a "Invite member" button.
  
  Form: email input + role selector (Member/Viewer, not Admin for security) + 
  "Send invitation" button. Submits POST /workspaces/{id}/invite.
  On success: close dialog, show toast "Invitation sent!".

STEP 7 — CREATE Workspaces/Settings.vue:
  File: resources/js/Pages/Workspaces/Settings.vue
  Layout: AuthenticatedLayout (title="Workspace Settings")
  
  Sections:
    1. "General" — workspace name field + save button
    2. "Members" — MemberList component + "Invite member" button (opens InviteMemberModal)
    3. "Danger Zone" — "Delete workspace" button (confirmation required, red border card)
  
  Each section is a card: rounded-lg border border-border bg-surface p-6 mb-4.

ACCEPTANCE CRITERIA:
  - /workspaces/create renders and creating a workspace redirects to /dashboard
  - /workspaces/{id}/settings renders for Admins, returns 403 for Members/Viewers
  - The workspace name updates correctly from the settings form
  - InviteMemberModal opens, submits, and shows a success toast
  - The MemberList displays all members with correct role badges
  - A non-admin visiting /workspaces/{id}/settings is redirected or shown a 403 page
```

---

# PHASE 4 — Projects
**Goal:** Project listing page and project creation modal.

## Antigravity Prompt — Phase 4

```
You are building Phase 4 of FocusFlow: the Projects index page.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  app/Models/Project.php
  app/Http/Controllers/Api/V1/ProjectController.php  (study existing logic)
  resources/js/Components/ColorIcon.vue              (the colored icon component)
  routes/web.php

STEP 2 — CREATE app/Http/Controllers/Web/ProjectController.php:
  Methods:
    - index($workspace): Inertia::render('Projects/Index', [
        'workspace' => $workspace,
        'projects'  => $workspace->projects()->withCount('tasks')->get(),
      ])
    - store($workspace): validate {name, description?}, create Project with a 
      randomly assigned accent color (store as `color` column — one of: red/orange/
      yellow/green/blue/purple/pink/gray). Redirect to /workspaces/{id}/projects 
      with flash success.
    - destroy($workspace, $project): Admin/Member only. Delete and redirect.

STEP 3 — ADD ROUTES:
  Under auth middleware:
    GET  /workspaces/{workspace}/projects      → ProjectController@index  (name: projects.index)
    POST /workspaces/{workspace}/projects      → ProjectController@store  (name: projects.store)
    DELETE /workspaces/{workspace}/projects/{project} → ProjectController@destroy

STEP 4 — CREATE ProjectCard.vue COMPONENT:
  File: resources/js/Components/ProjectCard.vue
  Props: project {id, name, color, tasks_count, description?}
  
  Renders a card (rounded-lg border border-border bg-surface shadow-sm p-5 
  hover:shadow-md transition cursor-pointer):
    - Top: ColorIcon (size=lg, the project's assigned color) 
    - Project name in font-display font-semibold
    - Optional description in text-muted text-sm (truncate to 2 lines)
    - Bottom: "{tasks_count} tasks" badge in gray
  
  Clicking navigates to /workspaces/{workspaceId}/projects/{projectId}.

STEP 5 — CREATE CreateProjectModal.vue COMPONENT:
  File: resources/js/Components/CreateProjectModal.vue
  Uses shadcn Dialog.
  
  Form:
    - Project name input (required)
    - Description textarea (optional, 3 rows)
    - Color picker: 8 colored circle buttons (the accent colors), click to select.
      Default: random. Selected state shows a checkmark.
    - Submit: "Create project"
  
  Submits POST /workspaces/{id}/projects. On success: closes dialog.

STEP 6 — CREATE Projects/Index.vue:
  File: resources/js/Pages/Projects/Index.vue
  Layout: AuthenticatedLayout (title="Projects")
  
  Content:
    - Page heading: workspace name + "Projects" in font-display
    - If Member/Admin: "New Project" button in top-right that opens CreateProjectModal
    - Grid of ProjectCard components (grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4)
    - Empty state (if no projects): centered illustration area with text "No projects yet.
      Create your first project to get started." + "Create project" button.

STEP 7 — WIRE UP SIDEBAR:
  Update AppSidebar.vue to now actually fetch and display projects for the current 
  workspace. The shared data in HandleInertiaRequests should include:
  
  'projects' => fn() => $request->user() && session('current_workspace_id')
    ? \App\Models\Workspace::find(session('current_workspace_id'))
        ?->projects()->select('id', 'name', 'color')->get()
    : []

  Update HandleInertiaRequests.php accordingly and use this in AppSidebar.vue.

ACCEPTANCE CRITERIA:
  - /workspaces/{id}/projects renders the project grid
  - CreateProjectModal opens, color picker works, submission creates a project
  - New project appears in the grid and in the sidebar without a full page reload
  - Empty state renders when no projects exist
  - Viewer role: "New Project" button is hidden (v-if="can('manage-projects')")
  - Each ProjectCard navigates to the correct Kanban URL on click
```

---

# PHASE 5 — Kanban Board ⭐
**Goal:** The centerpiece of FocusFlow. Drag-and-drop kanban board with columns for each status, real task cards, and RBAC-enforced read-only mode for Viewers.

## Antigravity Prompt — Phase 5

```
You are building Phase 5 of FocusFlow: THE KANBAN BOARD. This is the most important 
screen in the entire app. It must be polished, functional, and impressive.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  app/Models/Task.php                              (what fields does a task have?)
  app/Http/Controllers/Api/V1/TaskController.php  (understand move() and index())
  app/Models/Project.php
  resources/js/Composables/usePermissions.js
  package.json                                     (confirm vue-draggable-plus is installed)

STEP 2 — UNDERSTAND THE DATA MODEL:
  Read the Task model and migration to know the exact field names for:
    - status (the 4 values: backlog, in_progress, review, done — or whatever the 
      enum/string values are)
    - title
    - description
    - assignee relationship
    - priority (if it exists)
    - due_date (if it exists)
  
  This is critical. Do not assume field names — read the model first.

STEP 3 — CREATE app/Http/Controllers/Web/KanbanController.php:
  show($workspace, $project) method:
    - Authorize user can view this project in this workspace
    - Load all tasks for the project, eager-load assignee relationship
    - Group tasks by status
    - Return Inertia::render('Projects/Kanban', [
        'project'   => $project,
        'workspace' => $workspace,
        'columns'   => [
          ['id' => 'backlog',     'label' => 'Backlog',      'color' => '#6B7280', 'tasks' => $byStatus['backlog']     ?? []],
          ['id' => 'in_progress', 'label' => 'In Progress',  'color' => '#3B82F6', 'tasks' => $byStatus['in_progress'] ?? []],
          ['id' => 'review',      'label' => 'In Review',    'color' => '#F59E0B', 'tasks' => $byStatus['review']      ?? []],
          ['id' => 'done',        'label' => 'Done',         'color' => '#10B981', 'tasks' => $byStatus['done']        ?? []],
        ],
        'members'   => $workspace->users()->select('id', 'name')->get(),
      ])

  IMPORTANT: Use the actual status values from the Task model/enum.

STEP 4 — ADD ROUTE:
  GET /workspaces/{workspace}/projects/{project} → KanbanController@show (name: kanban.show)

STEP 5 — CREATE TaskCard.vue COMPONENT:
  File: resources/js/Components/TaskCard.vue
  Props: task {id, title, description?, assignee?, priority?, status}
  
  Design: White card, rounded-lg, border border-border, shadow-sm, p-3, mb-2.
  Hover: shadow-md, slight translate-y-[-1px].
  
  Contents:
    - Title: text-sm font-medium text-text, truncate to 2 lines
    - If assignee: small avatar (initials circle, 20px) in bottom-right
    - If priority: a small colored dot (high=red, medium=yellow, low=green) top-right
    - Task ID in font-mono text-xs text-muted bottom-left (e.g. "TASK-42")
  
  The cursor should be 'grab' when hovering (for drag affordance).
  In read-only mode (prop: readOnly=true), cursor is 'default', no grab.

STEP 6 — CREATE KanbanColumn.vue COMPONENT:
  File: resources/js/Components/KanbanColumn.vue
  Props: column {id, label, color, tasks[]}, readOnly (boolean)
  Emits: task-moved ({taskId, fromColumn, toColumn, newIndex})
  
  Design:
    - Column width: 280px, flex-shrink: 0
    - Header: colored left-border-4 (border-l-4) matching column.color, 
      column label in font-semibold, task count badge in gray circle
    - Body: min-height 400px, bg-surface-3/50, rounded-lg, p-2
    - Drop zone: when a card is dragged over, show a blue dashed border highlight
  
  Use vue-draggable-plus <VueDraggable> wrapping the task list:
    - group="tasks" (so cards can move between columns)
    - :disabled="readOnly"
    - On end event: emit 'task-moved' with the task id and new status (column id)
  
  "Add task" button at bottom of column (Members+ only, hidden for Viewers):
    Text: "+ Add task", text-muted, hover:text-text. Clicking emits 'create-task' 
    with the column's status.

STEP 7 — CREATE KanbanBoard.vue COMPONENT:
  File: resources/js/Components/KanbanBoard.vue
  Props: columns[], members[], readOnly (boolean)
  Emits: task-selected (taskId), create-task (status)
  
  Renders the columns in a horizontal flex row with overflow-x-auto.
  
  Handles the task-moved event from each column:
    - Optimistically update the local `columns` reactive data (move the task 
      object from one column's task array to another)
    - Call the API: axios.put(`/api/v1/workspaces/${workspaceId}/tasks/${taskId}/move`, 
      { status: newColumnId })
    - On API error: revert the optimistic update and show a toast error
  
  Note: The API call hits the existing /api/v1/ endpoint. Since we're using 
  session auth and Sanctum, add this to the axios call:
    axios.defaults.headers.common['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN');
  
  Also handle card clicks: emit 'task-selected' with the task id.

STEP 8 — CREATE Projects/Kanban.vue PAGE:
  File: resources/js/Pages/Projects/Kanban.vue
  Layout: AuthenticatedLayout
  
  Receives props: project, workspace, columns, members
  
  Page title: "{project.name}" with the project's ColorIcon next to it.
  
  Sub-header bar: project name breadcrumb + workspace name, and a 
  "New Task" button (Members+, shortcut for creating a task in Backlog).
  
  Renders <KanbanBoard> with:
    :columns="columns" (local reactive copy)
    :members="members"
    :read-only="isViewer"
    @task-selected="openTaskModal"
    @create-task="openCreateTaskModal"
  
  For now, openTaskModal and openCreateTaskModal can just log to console — 
  the Task modals are built in Phase 6.
  
  EMPTY STATE per column: if a column has no tasks, show a subtle 
  "No tasks here" text centered in the column body.

STEP 9 — UPDATE AppSidebar.vue:
  Make each project link in the sidebar navigate to the kanban URL:
  /workspaces/{workspaceId}/projects/{projectId}

ACCEPTANCE CRITERIA:
  - /workspaces/{id}/projects/{id} renders the 4-column kanban board
  - Tasks appear in their correct columns
  - Dragging a card between columns moves it visually
  - On drop, a PUT request fires to the API and the task persists on refresh
  - Optimistic update: the card moves instantly, doesn't wait for the API
  - On API failure: card snaps back to original column, error toast appears
  - Viewer role: cards are NOT draggable (cursor=default, no drag events)
  - "Add task" button is hidden for Viewers
  - Each column header shows the correct color accent and task count
  - Horizontal scrolling works when columns exceed the viewport width
  - The board looks impressive — this is the portfolio centerpiece
```

---

# PHASE 6 — Task Detail & Collaboration
**Goal:** Task creation modal, task detail slide-over, presence avatars showing who's viewing the same task in real-time.

## Antigravity Prompt — Phase 6

```
You are building Phase 6 of FocusFlow: Task modals and real-time collaboration.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  app/Models/Task.php                              (all fields)
  resources/js/Composables/useTaskUpdates.js       (understand how presence works)
  resources/js/Components/KanbanBoard.vue          (how it emits task-selected)
  resources/js/Pages/Projects/Kanban.vue

STEP 2 — CREATE PresenceAvatars.vue:
  File: resources/js/Components/PresenceAvatars.vue
  Props: taskId (number/string), currentUserId
  
  Uses the useTaskUpdates.js composable to join the presence channel for this task.
  
  Renders: a row of small (28px) overlapping avatar circles showing who else is 
  currently viewing this task. Each avatar: user's initials in a colored circle 
  (pick color based on user ID % 8 to select from accent colors).
  
  If only the current user is present: render nothing (don't show the user to themselves).
  If 2-4 others are present: show all avatars.
  If 5+ others: show first 3 + "+N" overflow badge.
  
  Tooltip on each avatar shows the user's name (shadcn Tooltip).
  
  Add a subtle "X users also viewing" text label to the right of the avatars in text-muted.

STEP 3 — CREATE TaskModal.vue:
  File: resources/js/Components/TaskModal.vue
  Uses shadcn Sheet (slide-over from the right, width ~540px).
  
  Props: task (object or null), projectId, workspaceId, mode ('view'|'create'), 
         initialStatus (for create mode), members[]
  Emits: close, task-created, task-updated, task-deleted
  
  CREATE MODE (when task is null / mode='create'):
    Simple form inside the sheet:
      - Title input (large, prominent, font-display)
      - Status selector (4 options with colored dots)
      - Assignee selector (dropdown of workspace members with avatars)
      - Priority selector (High/Medium/Low with colored indicators) — if field exists
      - Description textarea (optional)
      - Due date input (if field exists)
      - "Create task" button
    
    Submits POST to /api/v1/workspaces/{workspaceId}/projects/{projectId}/tasks
    On success: emit task-created with the new task object, close the sheet.
  
  VIEW/EDIT MODE (when task is provided):
    Top section:
      - Task ID in font-mono text-muted ("TASK-{id}")
      - PresenceAvatars component (shows who else is viewing)
      - Inline-editable title (click to edit → input → blur to save)
    
    Body (two-column on desktop):
      Left (wider): Description (inline-editable textarea)
      Right (narrower): Status, Assignee, Priority, Due date — each as a 
        clickable field that opens a mini popover to change the value.
    
    Changes auto-save with 500ms debounce using PATCH to 
    /api/v1/workspaces/{workspaceId}/projects/{projectId}/tasks/{taskId}
    Show a subtle "Saving..." / "Saved" indicator.
    
    Bottom: "Delete task" button (Members+ only) in text-red, with confirmation.

STEP 4 — UPDATE Projects/Kanban.vue:
  Import TaskModal and wire it up:
  
  - Add local state: showTaskModal=false, selectedTask=null, createTaskStatus=null
  - openTaskModal(taskId): fetch the task from the local columns data by id, 
    set selectedTask, set showTaskModal=true
  - openCreateTaskModal(status): set createTaskStatus=status, set selectedTask=null, 
    set showTaskModal=true
  
  Handle task-created event:
    Add the new task to the correct column's tasks array (optimistic).
  
  Handle task-updated event:
    Find the task in the columns data and update it in place.
    If status changed: move it to the new column.
  
  Handle task-deleted event:
    Remove the task from its column.
  
  Render <TaskModal> with all props and event handlers.

STEP 5 — UPDATE TaskCard.vue:
  Make cards clickable (emit 'task-clicked' with task id when card body is clicked, 
  NOT when dragging — use a flag to distinguish click from drag).

ACCEPTANCE CRITERIA:
  - Clicking a task card opens the TaskModal in view mode
  - The task detail shows all fields correctly
  - PresenceAvatars shows other connected users in real-time
  - If you open the same task in two browser windows, both show the other user's avatar
  - Inline editing saves changes automatically with debounce
  - "Saving..." indicator shows during save, "Saved ✓" shows on success
  - Clicking "Add task" in a column opens the modal in create mode with correct status pre-selected
  - Creating a task adds it to the correct column without page reload
  - Deleting a task removes it from the board without page reload
  - Task modal closes cleanly with Escape key or clicking outside
```

---

# PHASE 7 — Real-Time Wiring
**Goal:** Connect WebSocket events to the kanban board so it updates live without refresh. Wire the existing NotificationBell into the navbar.

## Antigravity Prompt — Phase 7

```
You are building Phase 7 of FocusFlow: Real-time event wiring.

The backend already broadcasts TaskMoved, TaskAssigned, and TaskCommented events 
on the workspace.{id} private channel via Reverb. Your job is to listen for those 
events in the Vue app and update the UI reactively.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  resources/js/Components/NotificationBell.vue  (understand what events it listens to)
  resources/js/echo.js                          (understand how Echo is configured)
  resources/js/Components/AppNavbar.vue
  resources/js/Pages/Projects/Kanban.vue
  resources/js/Components/KanbanBoard.vue
  app/Events/  (list all event files to see their channel + payload structure)

STEP 2 — UNDERSTAND THE EVENTS:
  Read each event class in app/Events/. For each, note:
    - The channel name (broadcastOn)
    - The event name (broadcastAs or class name)
    - The payload (broadcastWith or public properties)
  
  This is critical. Do not guess the event names or payload shape.

STEP 3 — WIRE NOTIFICATIONBELL INTO THE NAVBAR:
  NotificationBell.vue already exists and works. It just needs to be properly 
  mounted in AppNavbar.vue with the correct workspaceId prop.
  
  In AppNavbar.vue, ensure:
    - NotificationBell is imported
    - :workspace-id is set from $page.props.currentWorkspace?.id
    - It renders in the top-right of the navbar
    - On mobile it has appropriate spacing

STEP 4 — ADD REAL-TIME KANBAN UPDATES:
  In Projects/Kanban.vue, add a useEcho composable or inline Echo listener 
  in onMounted() that subscribes to the current workspace channel:
  
  window.Echo.private(`workspace.${props.workspace.id}`)
    .listen('TaskMoved', (e) => {
      // Find the task in columns by e.task.id
      // Remove it from its current column
      // Add it to the new column (e.task.status)
      // Show a subtle toast: "Task moved to [status]" if it wasn't moved by the current user
    })
    .listen('TaskAssigned', (e) => {
      // Find the task and update its assignee
    })
    .listen('TaskCommented', (e) => {
      // If the task detail modal is open for this task, 
      // add the comment to the comment list
    });
  
  Clean up the listener in onUnmounted().
  
  IMPORTANT: Use the EXACT event names from Step 2. Do not guess.

STEP 5 — ADD TOAST NOTIFICATIONS:
  Install and configure shadcn-vue's Sonner (or Toast) component.
  Add <Toaster /> to AuthenticatedLayout.vue.
  
  Create a simple useToast composable or use the shadcn toast directly.
  
  Add toasts for these actions:
    - Task created: "Task created ✓" (success, green)
    - Task moved (by someone else via WebSocket): "Alex moved a task to In Review"
    - Task assigned to current user: "You were assigned to '{task title}'" 
    - API errors: "Something went wrong. Please try again." (error, red)
    - Invitation sent: "Invitation sent to {email}" (success)
    - Settings saved: "Settings saved ✓" (success)

STEP 6 — ADD OPTIMISTIC UI POLISH:
  In KanbanBoard.vue, when a task is dragged:
    - During drag: show a subtle blue outline on the target column
    - On drop: the card should NOT flash or jump — it stays in place optimistically
    - If the API call fails within 3 seconds: snap the card back to original position 
      with a brief shake animation (CSS keyframe) and show error toast

ACCEPTANCE CRITERIA:
  - Open the kanban board in two browser tabs
  - Move a task in Tab A → it moves in Tab B within 1-2 seconds without refresh
  - The NotificationBell badge increments when a task event fires in another tab
  - Toasts appear for the defined actions and auto-dismiss after 4 seconds
  - No console errors related to Echo/Reverb when the channel is authenticated
  - Moving a task via Postman (hitting the API directly) updates the board in real-time
```

---

# PHASE 8 — Billing
**Goal:** Subscription status display, billing page for Admins, redirect to Stripe portal.

## Antigravity Prompt — Phase 8

```
You are building Phase 8 of FocusFlow: Billing and subscription management.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  app/Http/Controllers/Api/V1/WorkspaceController.php  (find the billingPortal method)
  app/Models/Workspace.php                             (check for Cashier/Billable trait)
  resources/js/Layouts/AuthenticatedLayout.vue
  routes/web.php

STEP 2 — CREATE app/Http/Controllers/Web/BillingController.php:
  Methods:
    - index($workspace): Admin only. Returns Inertia::render('Billing/Index', [
        'workspace'    => $workspace,
        'subscription' => $workspace->subscription('default'),  // or however Cashier is configured
        'onGracePeriod' => $workspace->subscription('default')?->onGracePeriod() ?? false,
        'plan'         => $workspace->subscribed() ? 'pro' : 'free',
      ])
    
    - portal($workspace): Admin only. Calls the existing billing portal logic 
      (from the API controller) and returns a redirect to the Stripe portal URL.

STEP 3 — ADD ROUTES:
  Under auth + Admin middleware:
    GET  /workspaces/{workspace}/billing         → BillingController@index  (name: billing.index)
    POST /workspaces/{workspace}/billing/portal  → BillingController@portal (name: billing.portal)

STEP 4 — CREATE Billing/Index.vue:
  File: resources/js/Pages/Billing/Index.vue
  Layout: AuthenticatedLayout (title="Billing")
  
  Design: Two sections.
  
  SECTION 1 — CURRENT PLAN CARD:
    Card (rounded-xl border-2 border-border p-6):
      If FREE plan:
        - Header with gray "Free" badge
        - Features list: "Up to 3 projects", "Up to 10 tasks", "Basic WebSockets"
        - CTA button: "Upgrade to Pro" in bg-primary text-white (prominent)
      
      If PRO plan:
        - Header with indigo "Pro" badge + "Active" in green
        - Features list: "Unlimited projects", "Unlimited tasks", 
          "Real-time collaboration", "Priority support"
        - "Manage subscription" button → submits POST to /billing/portal (opens Stripe)
        - If on grace period (cancelled but not expired): show amber warning 
          "Your Pro plan ends on {date}. Renew to keep access."
  
  SECTION 2 — PLAN COMPARISON (if on Free plan):
    Simple two-column table comparing Free vs Pro.

STEP 5 — ADD SUBSCRIPTION STATUS TO SIDEBAR:
  In AppSidebar.vue, add a small plan badge at the very bottom above the user info:
  
  If FREE: a subtle amber "Free Plan" pill with "Upgrade →" link (Admin only)
  If PRO: a subtle indigo "Pro" pill
  
  Pass the plan info via HandleInertiaRequests shared data:
  'plan' => fn() => ... check if current workspace is subscribed

ACCEPTANCE CRITERIA:
  - /workspaces/{id}/billing renders for Admins, 403 for others
  - The current plan is displayed correctly (Free or Pro)
  - "Manage subscription" button redirects to Stripe portal (test with Stripe test mode)
  - Sidebar shows the plan badge
  - Non-admin users do not see the Billing link in the sidebar
```

---

# PHASE 9 — Polish & Deployment Prep
**Goal:** Loading skeletons, empty states, transitions, mobile responsiveness, final visual review.

## Antigravity Prompt — Phase 9

```
You are building Phase 9 of FocusFlow: the polish pass. No new features — only 
quality, delight, and robustness.

STEP 1 — READ THESE FILES FIRST:
  DESIGN_SYSTEM.md
  Every file in resources/js/Pages/ and resources/js/Components/

STEP 2 — ADD LOADING SKELETONS:
  For each page that loads data from the server (Dashboard, Projects/Index, 
  Projects/Kanban, Workspaces/Settings, Billing/Index):
  
  Add a skeleton loading state using animated shimmer divs (use Tailwind's 
  animate-pulse on bg-surface-3 rounded-md placeholders).
  
  The Inertia progress bar (already configured with indigo color in Phase 0) 
  covers page transitions. Skeletons are for within-page data loading only 
  (e.g., when Axios calls are in flight for task details).

STEP 3 — ADD EMPTY STATES TO EVERY LIST:
  Check each page/component that renders a list. For each with no items:
    - Dashboard (no tasks): "You have no tasks yet. Ask your team to assign you some."
    - Projects/Index (no projects): "No projects yet." + illustration + "Create project" button
    - KanbanColumn (no tasks): Subtle "Drop tasks here" text with a dashed border hint
    - Workspaces (user has none): Redirect to /workspaces/create (already handled)
  
  Empty states should have a small icon/illustration (use a simple SVG or emoji), 
  a clear heading, a short explanation, and a CTA if one makes sense.

STEP 4 — ADD PAGE TRANSITIONS:
  In AuthenticatedLayout.vue, wrap <slot> with a Vue <Transition> component:
  
  <Transition name="page" mode="out-in">
    <slot :key="$page.url" />
  </Transition>
  
  CSS for .page-enter-active / .page-leave-active: 150ms ease transition on 
  opacity (0→1) and transform (translateY(4px)→translateY(0)).
  
  Keep it subtle — fast and barely noticeable. Professional, not flashy.

STEP 5 — MOBILE SIDEBAR:
  Update AppSidebar.vue and AuthenticatedLayout.vue for mobile:
  
  On mobile (< 768px):
    - Sidebar is hidden by default
    - A hamburger button appears in the navbar
    - Tapping opens the sidebar as an overlay (with a semi-transparent backdrop)
    - Tapping the backdrop or a nav link closes it
    
  Use @vueuse/core's useMediaQuery or a simple ref for the open/closed state.
  Sidebar slides in with a 200ms translate-x transition.

STEP 6 — ERROR HANDLING:
  Create a global error page: resources/js/Pages/Error.vue
  
  Handles HTTP errors that Inertia passes (404, 403, 500).
    - 404: "Page not found. Let's get you back on track." + "Go to Dashboard" button
    - 403: "You don't have permission to view this." + "Go back" button
    - 500: "Something went wrong on our end." + "Go to Dashboard" button
  
  Register this in app.js as the resolveErrors handler.

STEP 7 — FINAL VISUAL QA CHECKLIST:
  Go through every screen and verify:
    [ ] All text uses the correct font (Inter for UI, Plus Jakarta Sans for headings)
    [ ] All cards have: rounded-lg border border-border shadow-sm
    [ ] All primary buttons: bg-primary text-white hover:bg-primary-dark
    [ ] All inputs: rounded-md border border-border focus:ring-1 focus:ring-primary
    [ ] Sidebar active state is consistent across all nav items
    [ ] ColorIcon appears on all workspace and project references
    [ ] Role restrictions are consistently applied (Viewer = read-only everywhere)
    [ ] The NotificationBell badge works correctly
    [ ] No hardcoded workspace/project IDs in links (always use dynamic IDs)
    [ ] All flash messages show as toasts
    [ ] Page titles in the browser tab are set correctly ("{page} — FocusFlow")

STEP 8 — PREPARE FOR DEPLOYMENT:
  Create a DEPLOYMENT.md file in the project root with:
    - Required environment variables (.env keys for production)
    - npm run build command
    - php artisan migrate --force command sequence
    - Note that Reverb must be running as a daemon in production
    - Note that Horizon must be running as a daemon in production
    - Recommended: deploy on Laravel Forge or Fly.io

ACCEPTANCE CRITERIA:
  - No layout shifts or flash of unstyled content on any page
  - Loading skeletons appear during data fetching
  - Every list has an appropriate empty state
  - Page transitions are smooth and fast
  - Mobile sidebar opens/closes correctly at <768px viewport
  - 404 and 403 errors show the custom error page
  - npm run build completes with no errors
  - php artisan route:list shows all web and api routes cleanly
  - The live preview URL works: login → create workspace → create project → 
    see kanban → drag a task → notification bell updates. The full demo flow works.
```

---

## Pre-Launch Demo Script
> Use this to verify the app works end-to-end before sharing with interviewers.

```
1. Register a new account → redirected to /dashboard → empty state shown
2. Create a workspace "Acme Corp" → workspace appears in sidebar
3. Create a project "Website Redesign" (blue icon) → appears in project grid
4. Navigate to the kanban board → 4 empty columns visible
5. Add 3 tasks to Backlog via the "+ Add task" button
6. Drag a task from Backlog to "In Progress" → confirm it stays after refresh
7. Open two browser tabs, drag a task in Tab A → verify it moves in Tab B (real-time)
8. Open a task card → see the task detail slide-over
9. Open the same task in Tab B → see the presence avatar appear in Tab A
10. Invite a team member (if email configured, else just check the API fires)
11. Visit /billing as admin → see the plan card
12. Check NotificationBell: the count should have incremented from the task events
13. Shrink the browser to mobile width → verify the sidebar hamburger works
14. Log out → verify redirect to /login → verify /dashboard redirects to /login
```

---

## Phase Execution Order

| Phase | Name | Key Output | Dependencies |
|---|---|---|---|
| 0 | Foundation | Inertia installed, design system ready | None |
| 1 | Auth | Login + Register pages | Phase 0 |
| 2 | App Shell | Sidebar, navbar, layout, dashboard | Phase 1 |
| 3 | Workspaces | Creation + settings pages | Phase 2 |
| 4 | Projects | Project listing + cards | Phase 2, 3 |
| 5 | Kanban Board ⭐ | Drag-and-drop board | Phase 4 |
| 6 | Task Detail | Modal, presence avatars | Phase 5 |
| 7 | Real-Time | Live board updates + toasts | Phase 5, 6 |
| 8 | Billing | Stripe portal + plan display | Phase 3 |
| 9 | Polish | Skeletons, mobile, transitions | All phases |

**Do not skip ahead.** Each phase builds on the previous. The most common mistake is jumping to Phase 5 (kanban) before the layout and routing infrastructure from Phase 2 is solid.

---

## 📊 Frontend Progress Tracker

> Update status as phases complete. This is the **authoritative** frontend phase tracker.
> Backend progress is in [`PLAN.md`](./PLAN.md) (separate, already complete).

| Phase | Name | Status | Commit Range | Tag |
|-------|------|--------|--------------|-----|
| 0 | Foundation (Inertia + shadcn + design system) | ✅ Complete | `a1da131` → `f7b8f07` | — |
| 1 | Authentication (Login + Register pages) | ✅ Complete | `cda0b4c` → `3c2dd49` | — |
| 2 | App Shell (Sidebar, Navbar, Dashboard) | ✅ Complete | `0e33109` → `2c6a936` | `v0.2.0-frontend-phase-2` |
| 3 | Workspaces (Create, Settings, Members, Invite) | ✅ Complete | `4451d47` → `77c7398` | `v0.3.0-frontend-phase-3` |
| 4 | Projects (Index, ProjectCard, Create modal) | 🔄 In progress | — | — |
| 5 | Kanban Board ⭐ (Drag-and-drop board) | ⬜ Not started | — | — |
| 6 | Task Detail (Modal, presence avatars) | ⬜ Not started | — | — |
| 7 | Real-Time Wiring (Live updates + toasts) | ⬜ Not started | — | — |
| 8 | Billing (Stripe portal + plan display) | ⬜ Not started | — | — |
| 9 | Polish (Skeletons, mobile, transitions) | ⬜ Not started | — | — |

> Update status: ⬜ Not started → 🔄 In progress → ✅ Complete

