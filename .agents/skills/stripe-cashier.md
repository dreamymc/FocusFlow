---
name: stripe-cashier
description: >
  Laravel Cashier + Stripe billing patterns. Subscription management, webhook handling,
  plan gating. Load before any billing or payment work.
compatible_agents:
  - integration-specialist
  - backend-engineer
---

# Stripe / Cashier Skill

## Billable Setup
```php
// app/Models/Workspace.php
use Laravel\Cashier\Billable;
class Workspace extends Model
{
    use Billable;
}
```

## SubscribeWorkspaceAction
```php
final class SubscribeWorkspaceAction
{
    public function execute(Workspace $workspace, string $stripePaymentMethodId, string $plan): Subscription
    {
        return $workspace
            ->newSubscription('default', config("plans.{$plan}.stripe_price_id"))
            ->create($stripePaymentMethodId);
    }
}
```

## Plan Gating
```php
// app/Http/Middleware/RequireProPlan.php
public function handle(Request $request, Closure $next): Response
{
    $workspace = $request->route('workspace');
    if (! $workspace->subscribed('default')) {
        return response()->json(['message' => 'Pro plan required.'], 402);
    }
    return $next($request);
}
```

## Webhook Handler
```php
// app/Http/Controllers/Webhooks/StripeController.php
// Extends Cashier's built-in webhook controller
class StripeController extends WebhookController
{
    public function handleInvoicePaymentSucceeded(array $payload): Response
    {
        // Cashier handles this automatically — only override if custom logic needed
        return $this->successMethod();
    }
}
```

## Testing Webhooks
```php
it('activates pro plan on successful payment', function () {
    Http::fake(['https://api.stripe.com/*' => Http::response(['status' => 'active'])]);

    $workspace = Workspace::factory()->create();

    $this->postJson('/stripe/webhook', [
        'type' => 'invoice.payment_succeeded',
        'data' => ['object' => ['customer' => $workspace->stripe_id]],
    ])->assertOk();
});
```
