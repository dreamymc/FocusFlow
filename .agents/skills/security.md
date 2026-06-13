---
name: security
description: >
  Security checklist and patterns for Laravel 11 APIs. Auth hardening,
  rate limiting, header security, OWASP basics. Load before any auth work.
compatible_agents:
  - security-auditor
  - backend-engineer
---

# Security Skill

## Required Middleware Stack
```php
// app/Http/Kernel.php — api middleware group
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\SetSecurityHeaders::class,  // Custom — always include
],
```

## Security Headers Middleware
```php
// app/Http/Middleware/SetSecurityHeaders.php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    return $response;
}
```

## Token Expiry
```php
// config/sanctum.php
'expiration' => 60 * 24 * 30,  // 30 days in minutes
```

## Brute Force Protection
```php
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)
        ->by($request->ip())
        ->response(fn() => response()->json(['message' => 'Too many attempts.'], 429));
});
```

## IDOR Prevention
Every query MUST scope to the authenticated workspace:
```php
// ✅ SAFE
Task::forWorkspace($request->route('workspace'))->findOrFail($id);

// ❌ VULNERABLE — user can access any workspace's tasks
Task::findOrFail($id);
```
