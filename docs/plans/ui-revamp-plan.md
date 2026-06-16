# UI Revamp Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Overhaul the FocusFlow UI/UX to transform it from a generic layout into a craft-driven, premium product interface with distinct character, custom scrollbars, cohesive typography, and refined micro-interactions.

**Architecture:** We will maintain the Laravel 11, Inertia.js, and Vue 3 tech stack. Visual styling will be implemented using Vanilla CSS (extended utilities/tokens inside global stylesheet) and customized Tailwind utility classes without altering backend routes, controllers, or database schemas.

**Tech Stack:** Laravel 11 · Inertia.js · Vue 3 · Tailwind CSS · Lucide icons

---

### Task 1: Global CSS Foundation Overhaul

**Files:**
- Modify: `resources/css/app.css`

**Step 1: Write Custom Scrollbar and Typography Classes**
We will add standard scrollbar styling (6px wide, transparent track, light gray rounded thumb, primary tint on hover), Plus Jakarta Sans display font styles, JetBrains Mono font utilities, and smooth page transition base definitions.

```css
/* Custom Scrollbars */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 9999px;
}
::-webkit-scrollbar-thumb:hover {
  background: #6366F1;
}

/* Typography Sizing System */
.font-display-title {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 1.5rem; /* 24px */
  font-weight: 700;
  letter-spacing: -0.02em;
}

.text-micro-mono {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.6875rem; /* 11px */
  color: #94A3B8;
}

.label-uppercase-tracked {
  font-size: 0.6875rem; /* 11px */
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 600;
  color: #475569;
}
```

**Step 2: Verify and Commit**
Verify that the CSS builds correctly.
Run: `pnpm run build`
Expected: Builds with zero errors.

Commit:
```bash
git add resources/css/app.css
git commit -m "style: revamp global css with scrollbars, typography, and focus states"
```

---

### Task 2: Auth Pages Overhaul

**Files:**
- Modify: `resources/js/Layouts/GuestLayout.vue`
- Modify: `resources/js/Pages/Auth/Login.vue`
- Modify: `resources/js/Pages/Auth/Register.vue`

**Step 1: Design Premium Left Panel in GuestLayout**
Replace the flat indigo left panel with a deep dark background (`#0F0F1A`), offset radial gradients, blurred geometric shapes, and a high-craft tagline section including a designer-grade quote or detail.

**Step 2: Customize Input Floating Labels and Shimmer Submit Buttons**
Enhance input fields in `Login.vue` and `Register.vue` to have smooth transition border colors and input focus. Add a subtle hover shimmer animation to the submit button with absolute width to avoid shifting layout on loading state.

**Step 3: Verify and Commit**
Run: `pnpm run build`
Expected: Success.

Commit:
```bash
git add resources/js/Layouts/GuestLayout.vue resources/js/Pages/Auth/Login.vue resources/js/Pages/Auth/Register.vue
git commit -m "ui: revamp auth guest layout, login, and registration pages"
```

---

### Task 3: App Shell & Navigation Overhaul

**Files:**
- Modify: `resources/js/Components/AppSidebar.vue`
- Modify: `resources/js/Components/AppNavbar.vue`

**Step 1: Signature Sidebar Styling**
Add top-to-bottom subtle gradient sidebar background. Adjust active nav item to have a 3-4px solid primary left border and background bleed. Style workspace icon box-shadow glow. Integrate projects section with small accent colored dots. Place user info in a dark panel zone at the bottom. Implement smooth width collapse toggle.

**Step 2: Navbar Polish**
Add border-b hairline and sticky headers. Enhance avatar and notification button hover states.

**Step 3: Verify and Commit**
Run: `pnpm run build`
Expected: Success.

Commit:
```bash
git add resources/js/Components/AppSidebar.vue resources/js/Components/AppNavbar.vue
git commit -m "ui: implement signature sidebar and navbar visual design"
```

---

### Task 4: Dashboard Page Revamp

**Files:**
- Modify: `resources/js/Pages/Dashboard.vue`

**Step 1: Unbalance Stat Cards & Micro-typography**
Make the "Total Tasks" card display size larger. Use display typography for numbers (~36px) and upper-case tracking for labels (11px). Add subtle divider lines. Implement timeline recent activity section.

**Step 2: Verify and Commit**
Run: `pnpm run build`
Expected: Success.

Commit:
```bash
git add resources/js/Pages/Dashboard.vue
git commit -m "ui: revamp dashboard stats cards and timeline list"
```

---

### Task 5: Kanban Board, Columns, and Task Cards

**Files:**
- Modify: `resources/js/Pages/Projects/Kanban.vue`
- Modify: `resources/js/Components/KanbanBoard.vue`
- Modify: `resources/js/Components/KanbanColumn.vue`
- Modify: `resources/js/Components/TaskCard.vue`

**Step 1: Style Task Cards with Priority Borders and Hover Lifting**
Modify `TaskCard.vue` to include a 4px left-border colored by task priority (high: red, medium: yellow, low: green/blue). Add subtle transform scale/lift on hover (`translate-y-[-2px] hover:shadow-md`) and cursor grabbing state. Update assignee initials to overlap.

**Step 2: Style Columns with Alternating Tints and Droppable Dashed States**
In `KanbanColumn.vue`, style status headers to stay sticky. Give column backgrounds a barely noticeable status tint. Define visible dashed border lines when dragging tasks over columns. Implement dashed add task buttons.

**Step 3: Verify and Commit**
Run: `pnpm run build`
Expected: Success.

Commit:
```bash
git add resources/js/Pages/Projects/Kanban.vue resources/js/Components/KanbanBoard.vue resources/js/Components/KanbanColumn.vue resources/js/Components/TaskCard.vue
git commit -m "ui: upgrade kanban board columns, headers, and task cards"
```

---

### Task 6: Task Modal Details and Comments

**Files:**
- Modify: `resources/js/Components/TaskModal.vue`

**Step 1: Sheet Transition and Visual Improvements**
Refine task details sheet spacing, auto-save spinner, priority dropdowns, and comment log layout. Make details header and input fields appear premium.

**Step 2: Verify and Commit**
Run: `pnpm run build`
Expected: Success.

Commit:
```bash
git add resources/js/Components/TaskModal.vue
git commit -m "ui: polish task edit sheet details and comments layout"
```

---

### Task 7: Empty States & Notification Bell Dropdown

**Files:**
- Modify: `resources/js/Components/NotificationBell.vue`

**Step 1: Notification Dropdown & Badge Design**
Make badge count use primary color (indigo) instead of red, pulsing when count > 0. Refine the list styles and header/unread states. Add geometric SVG to the empty state of notifications.

**Step 2: Verify and Commit**
Run: `pnpm run build`
Expected: Success.

Commit:
```bash
git add resources/js/Components/NotificationBell.vue
git commit -m "ui: design premium notifications bell, badge pulse, and dropdown list"
```

---

### Task 8: Verification, Test Suite, and Audit

**Files:**
- Run verification command line checks, tests, build checks.

**Step 1: Verify all tests and code linting**
Run: `php artisan test`
Expected: All Pest tests pass.
Run: `pnpm run build`
Expected: Front-end production assets build perfectly.

**Step 2: Clean up and audit using pre-push check**
Run final pre-push codebase audit to clean up debug snippets, junk imports, or formatting inconsistencies.
