# Phase 1 Authentication Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build Login and Register pages and wire Laravel session-based authentication routes for the Inertia web frontend.

**Architecture:** Create specialized controllers under `app/Http/Controllers/Web/Auth/` to handle Laravel standard web session authentication and redirect requests. Define guest and auth route groups in `routes/web.php`. Build a full-screen split layout (`GuestLayout.vue`) and implement reactive forms (`Login.vue` and `Register.vue`) using `@inertiajs/vue3` `useForm` utility.

**Tech Stack:** Laravel 11, Inertia.js, Vue 3, Tailwind CSS v4, shadcn-vue

---

### Task 1: Create Web Auth Controllers

**Files:**
- Create: `app/Http/Controllers/Web/Auth/LoginController.php`
- Create: `app/Http/Controllers/Web/Auth/RegisterController.php`
- Create: `app/Http/Controllers/Web/Auth/LogoutController.php`

**Step 1: Create LoginController.php**
Write `app/Http/Controllers/Web/Auth/LoginController.php` containing:
- `create()`: returns `Inertia::render('Auth/Login')`
- `store()`: validates email and password, attempts `Auth::attempt()`, redirects to `/dashboard` on success or returns back with errors on failure.

**Step 2: Create RegisterController.php**
Write `app/Http/Controllers/Web/Auth/RegisterController.php` containing:
- `create()`: returns `Inertia::render('Auth/Register')`
- `store()`: validates user registration inputs, creates the User model, signs the user in via `Auth::login()`, and redirects to `/dashboard`.

**Step 3: Create LogoutController.php**
Write `app/Http/Controllers/Web/Auth/LogoutController.php` containing:
- `destroy()`: logs the user out, invalidates and regenerates the session token, and redirects to `/login`.

**Step 4: Commit**
Run: `git add app/Http/Controllers/Web/Auth && git commit -m "feat(auth): create web auth controllers"`

---

### Task 2: Configure Web Authentication Routes

**Files:**
- Modify: `routes/web.php`

**Step 1: Register routes in routes/web.php**
Expose `/login` and `/register` for guest users, and `/logout` for authenticated users.

**Step 2: Commit**
Run: `git add routes/web.php && git commit -m "feat(auth): register guest and auth web routes"`

---

### Task 3: Create GuestLayout.vue

**Files:**
- Create: `resources/js/Layouts/GuestLayout.vue`

**Step 1: Create GuestLayout component**
Develop a split-screen design.
- Left half (hidden on mobile): brand panel with background color `#6366F1`, a geometric background overlay, the "FocusFlow" logo, and the tagline "Where great teams ship."
- Right half: centered white card displaying slot content.

**Step 2: Commit**
Run: `git add resources/js/Layouts/GuestLayout.vue && git commit -m "feat(auth): create guest layout component"`

---

### Task 4: Create Login Page

**Files:**
- Create: `resources/js/Pages/Auth/Login.vue`

**Step 1: Create Login Page component**
Develop the login screen utilizing `GuestLayout`. Use `useForm` from `@inertiajs/vue3` to handle inputs (email, password), validation errors, and submission. Add flash and field error message displays. Follow design system spacing and components (button, input, label, etc.).

**Step 2: Commit**
Run: `git add resources/js/Pages/Auth/Login.vue && git commit -m "feat(auth): create login page component"`

---

### Task 5: Create Register Page

**Files:**
- Create: `resources/js/Pages/Auth/Register.vue`

**Step 1: Create Register Page component**
Develop the user registration screen using `GuestLayout`. Bind form inputs (name, email, password, password_confirmation) using `useForm` and add error handlers.

**Step 2: Commit**
Run: `git add resources/js/Pages/Auth/Register.vue && git commit -m "feat(auth): create register page component"`
