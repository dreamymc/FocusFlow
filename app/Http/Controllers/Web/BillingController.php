<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request, Workspace $workspace)
    {
        if (!$request->user()->hasRole(WorkspaceRole::Admin->value)) {
            abort(403, 'Only workspace admins can access the billing page.');
        }

        $subscription = $workspace->subscription('default');
        
        return Inertia::render('Billing/Index', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
            ],
            'subscription' => $subscription ? [
                'name' => $subscription->type,
                'stripe_status' => $subscription->stripe_status,
                'ends_at' => $subscription->ends_at ? $subscription->ends_at->toDateString() : null,
            ] : null,
            'onGracePeriod' => $subscription?->onGracePeriod() ?? false,
            'plan' => $workspace->subscribed('default') ? 'pro' : 'free',
        ]);
    }

    public function portal(Request $request, Workspace $workspace)
    {
        if (!$request->user()->hasRole(WorkspaceRole::Admin->value)) {
            abort(403, 'Only workspace admins can access the billing portal.');
        }

        // Ensure Stripe customer is created/retrieved
        $workspace->createOrGetStripeCustomer();

        // Redirect to Stripe portal
        $url = $workspace->billingPortalUrl(route('dashboard'));

        return Inertia::location($url);
    }
}
