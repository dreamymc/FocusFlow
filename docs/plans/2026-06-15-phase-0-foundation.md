# Phase 0 Foundation Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Install Inertia.js, shadcn-vue, configure the design system, and replace the Inertia entry point.

**Architecture:** Setup Inertia.js on both backend and frontend, replace the root welcome view with the Inertia app layout, initialize shadcn-vue, and configure design system styling with Tailwind.

**Tech Stack:** Laravel 11, Inertia.js, Vue 3, Tailwind CSS v4, shadcn-vue

---

### Task 1: Install Backend Packages and Register Middleware

**Files:**
- Modify: `bootstrap/app.php`
- Modify: `composer.json` (will be updated by composer require)

**Step 1: Install Inertia Laravel Package**
Run: `composer require inertiajs/inertia-laravel`
Expected: Installation completes successfully.

**Step 2: Generate Inertia Middleware**
Run: `php artisan inertia:middleware`
Expected: `app/Http/Middleware/HandleInertiaRequests.php` is generated.

**Step 3: Register Middleware in bootstrap/app.php**
Modify `bootstrap/app.php` to append `HandleInertiaRequests` middleware to the web group after `SubstituteBindings`.
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\HandleInertiaRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
        $middleware->alias([
            'scope.workspace' => \App\Http\Middleware\WorkspaceScope::class,
        ]);
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\SetSecurityHeaders::class,
        ]);
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
```

**Step 4: Commit changes**
Run: `git add bootstrap/app.php app/Http/Middleware/HandleInertiaRequests.php composer.json composer.lock && git commit -m "chore: install inertia backend and register middleware"`

---

### Task 2: Install Frontend Packages

**Files:**
- Modify: `package.json`

**Step 1: Install frontend dependencies**
Run: `npm install @inertiajs/vue3 @vueuse/core vue-draggable-plus`
Expected: Command completes successfully.

**Step 2: Commit changes**
Run: `git add package.json pnpm-lock.yaml && git commit -m "chore: install frontend packages"`

---

### Task 3: Initialize shadcn-vue & Install Components

**Files:**
- Modify: `tailwind.config.js` (will be updated/created)
- Create: components in `resources/js/Components/ui/`
- Create: `resources/js/lib/utils.js`

**Step 1: Inspect shadcn-vue init help**
Run: `npx shadcn-vue@latest init --help`
Expected: Help documentation printed.

**Step 2: Run shadcn-vue init**
Run: `npx shadcn-vue@latest init -y` or interactively if needed, answering:
- TypeScript: No
- style: default
- base color: slate
- CSS variables: yes
- tailwind config: tailwind.config.js
- components alias: @/Components/ui
- utils alias: @/lib/utils
Wait, if interactive, we can run it via terminal. Let's make sure it completes.

**Step 3: Add shadcn-vue components**
Run: `npx shadcn-vue@latest add button input label textarea badge avatar card dialog sheet dropdown-menu separator tooltip toast sonner -y`
Expected: All components are downloaded and saved to `resources/js/Components/ui/` or `resources/js/lib/utils.js`.

**Step 4: Commit changes**
Run: `git add resources/js/Components/ui resources/js/lib tailwind.config.js package.json && git commit -m "chore: initialize shadcn-vue and install base components"`

---

### Task 4: Create Inertia Blade Template & Entrypoint

**Files:**
- Create: `resources/views/app.blade.php`
- Modify: `resources/js/app.js`

**Step 1: Create resources/views/app.blade.php**
Write:
```html
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
```

**Step 2: Rewrite resources/js/app.js**
Write:
```javascript
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
```

**Step 3: Commit changes**
Run: `git add resources/views/app.blade.php resources/js/app.js && git commit -m "feat: add app.blade.php and setup app.js entrypoint"`

---

### Task 5: Update Tailwind Configuration and CSS

**Files:**
- Modify: `tailwind.config.js`
- Modify: `resources/css/app.css`

**Step 1: Update tailwind.config.js**
Extend colors and fonts according to the design system.
```javascript
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
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
    },
  },
  plugins: [],
}
```

**Step 2: Rewrite resources/css/app.css**
Write:
```css
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
```

**Step 3: Commit changes**
Run: `git add tailwind.config.js resources/css/app.css && git commit -m "style: update tailwind config and css layout for design system"`

---

### Task 6: Configure Inertia Middleware Share & Web Routes

**Files:**
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Modify: `routes/web.php`

**Step 1: Update HandleInertiaRequests.php**
Update `share` method to include auth and flash.
```php
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
        ]);
    }
```

**Step 2: Update routes/web.php**
Replace contents with:
```php
<?php

use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;
use App\Http\Controllers\Webhooks\StripeController;

// Stripe webhook — must be BEFORE auth middleware
Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook'])
    ->middleware(VerifyWebhookSignature::class);

// Auth routes (Phase 1)
// App routes (Phase 2+)
```

**Step 3: Commit changes**
Run: `git add app/Http/Middleware/HandleInertiaRequests.php routes/web.php && git commit -m "feat: configure inertia middleware share and web routes"`

---

### Task 7: Create DESIGN_SYSTEM.md & Clean up unused welcome view

**Files:**
- Create: `DESIGN_SYSTEM.md`
- Delete: `resources/views/welcome.blade.php`

**Step 1: Create DESIGN_SYSTEM.md**
Write specifications for colors, fonts, statuses, workspace icons, and other UI rules.

**Step 2: Delete welcome.blade.php**
Run: `rm resources/views/welcome.blade.php`

**Step 3: Commit changes**
Run: `git add DESIGN_SYSTEM.md && git rm resources/views/welcome.blade.php && git commit -m "docs: add design system spec and clean up unused welcome view"`
