<?php

use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows workspace admins to view the billing page', function () {
    [$workspace, $user] = createWorkspaceWithUser(WorkspaceRole::Admin);

    $response = $this->actingAs($user)
        ->get(route('billing.index', $workspace));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Billing/Index')
        ->where('plan', 'free')
        ->where('onGracePeriod', false)
    );
});

it('returns 403 when non-admins try to view the billing page', function () {
    [$workspace, $user] = createWorkspaceWithUser(WorkspaceRole::Member);

    $response = $this->actingAs($user)
        ->get(route('billing.index', $workspace));

    $response->assertForbidden();
});

it('redirects workspace admin to stripe billing portal on post', function () {
    [$workspace, $user] = createWorkspaceWithUser(WorkspaceRole::Admin);
    $workspace->update(['stripe_id' => 'cus_123']);

    // Partial mock to bypass actual Stripe API call
    $workspaceMock = Mockery::mock($workspace)->makePartial();
    $workspaceMock->shouldReceive('createOrGetStripeCustomer');
    $workspaceMock->shouldReceive('billingPortalUrl')
        ->andReturn('https://checkout.stripe.com/portal/session_test123');

    \Illuminate\Support\Facades\Route::bind('workspace', function ($value) use ($workspaceMock, $workspace) {
        return $value == $workspace->id ? $workspaceMock : Workspace::findOrFail($value);
    });

    $response = $this->actingAs($user)
        ->post(route('billing.portal', $workspace));

    $response->assertRedirect('https://checkout.stripe.com/portal/session_test123');
});
