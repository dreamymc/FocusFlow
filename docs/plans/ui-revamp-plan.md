# FocusFlow UI Revamp Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Revamp the FocusFlow UI to look like a premium, craft-driven SaaS product with distinct dark/light mode, smooth transitions, custom scrollbars, and high-fidelity page components.

**Architecture:** We will implement CSS variables in resources/css/app.css for theme-aware tokens (bg-surface, text-text, etc.) and bind the active state to the `.dark` class toggled on the root document. The app shell, authentication pages, dashboard, and kanban board will be refactored to consume these design system tokens and showcase premium details (e.g. gradients, floating labels, timeline items, responsive columns, and avatar groups).

**Tech Stack:** Vue 3, Inertia.js, Tailwind CSS v4, Lucide Vue Icons, Vue Draggable Plus.

---

### Task 1: CSS Theme System & Utilities
**Files:**
- Modify: [app.css](file:///home/visionmc/projects/focusflow/resources/css/app.css)
- Create: [useTheme.js](file:///home/visionmc/projects/focusflow/resources/js/Composables/useTheme.js)
- Modify: [app.blade.php](file:///home/visionmc/projects/focusflow/resources/views/app.blade.php)

**Step 1: Set up CSS variables for Light and Dark themes**
Define standard theme variables under `:root` and `.dark` selectors inside resources/css/app.css. Support scrollbars, typography scales, active borders, and focus rings. Prevent light/dark flashing by injecting a tiny blocker script in app.blade.php.
**Step 2: Create a Vue theme composable**
Implement a composable that exposes the active theme ('light' | 'dark'), a toggle function, and initializes theme preference in localStorage.

---

### Task 2: Auth Pages Overhaul
**Files:**
- Modify: [GuestLayout.vue](file:///home/visionmc/projects/focusflow/resources/js/Layouts/GuestLayout.vue)
- Modify: [Login.vue](file:///home/visionmc/projects/focusflow/resources/js/Pages/Auth/Login.vue)
- Modify: [Register.vue](file:///home/visionmc/projects/focusflow/resources/js/Pages/Auth/Register.vue)

**Step 1: Revamp GuestLayout**
Left pane: Dark slate base (#0f172a / #020617) with layered radial gradients, floating/rotated geometric boxes, and testimonials/quotes. Right pane: Smooth surface, styled form card container, and responsive spacing.
**Step 2: Revamp Login & Register forms**
Floating input labels that translate smoothly up on focus or text presence. Submit buttons with hover shimmer, scaling transition, and loading spinner animation.

---

### Task 3: App Shell & Navigation (Sidebar, Navbar, Theme Toggle)
**Files:**
- Modify: [AppSidebar.vue](file:///home/visionmc/projects/focusflow/resources/js/Components/AppSidebar.vue)
- Modify: [AppNavbar.vue](file:///home/visionmc/projects/focusflow/resources/js/Components/AppNavbar.vue)
- Modify: [AuthenticatedLayout.vue](file:///home/visionmc/projects/focusflow/resources/js/Layouts/AuthenticatedLayout.vue)

**Step 1: AppSidebar Revamp**
- Background: subtle gradient top-to-bottom.
- Active items: 3px solid primary border and bleeding highlight background.
- Project dots: small custom color badges.
- User area: dark card block at the bottom.
- Hover states: transition background 150ms.
**Step 2: AppNavbar Theme Switcher**
Insert a theme switch toggle button in the navbar (Sun/Moon icons) connected to our useTheme composable. Keep the navbar blur styling robust.

---

### Task 4: Dashboard Revamp
**Files:**
- Modify: [Dashboard.vue](file:///home/visionmc/projects/focusflow/resources/js/Pages/Dashboard.vue)

**Step 1: Unbalanced Stats Grid**
- First stat (Total Tasks) is double the width (spans 2), features larger font-display size, and distinct accent background.
- Other stats (In Progress, Completed Today) are simple balanced cards.
**Step 2: Activity Timeline**
Redesign the list of recent tasks using a left timeline border with colored status indicators (backlog, in progress, review, done). Remove any table borders.

---

### Task 5: Kanban Board & Task Cards
**Files:**
- Modify: [Kanban.vue](file:///home/visionmc/projects/focusflow/resources/js/Pages/Projects/Kanban.vue)
- Modify: [KanbanBoard.vue](file:///home/visionmc/projects/focusflow/resources/js/Components/KanbanBoard.vue)
- Modify: [KanbanColumn.vue](file:///home/visionmc/projects/focusflow/resources/js/Components/KanbanColumn.vue)
- Modify: [TaskCard.vue](file:///home/visionmc/projects/focusflow/resources/js/Components/TaskCard.vue)

**Step 1: TaskCard Polish**
- 4px left-border reflecting priority (High: red, Medium: yellow, Low: green).
- Overdue warnings in styled capsule pill badges.
- Overlapping user initials avatar group with white ring spacers.
- Lift transitions on hover.
**Step 2: KanbanColumn Tints & Drop Zones**
- Alternating subtle tints per column status.
- Dashed drop insertion lines when a card is dragged over.
- Sticky column headers so they don't scroll away.

---

### Task 6: Verification & Compilation
**Files:**
- Verify: [Dashboard.vue](file:///home/visionmc/projects/focusflow/resources/js/Pages/Dashboard.vue)
- Verify: [Kanban.vue](file:///home/visionmc/projects/focusflow/resources/js/Pages/Projects/Kanban.vue)

**Step 1: Adjust Skeleton Loaders**
- Ensure all loading placeholders (Dashboard skeletons, Kanban skeletons, Notification bell skeleton) use theme-aware classes (e.g. `bg-surface-3/50` or `bg-slate-800/40` in dark mode) to completely prevent white flashes during initial mount.
**Step 2: Compile assets**
- Run `pnpm run build` to verify webpack/vite compiles with zero errors.
**Step 3: Run backend feature tests**
- Run `php artisan test` to verify no routes, middlewares, or controllers were broken.
