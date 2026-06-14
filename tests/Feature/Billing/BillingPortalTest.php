<?php

use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;

it('returns a billing portal url', function () {
    $workspace = Workspace::factory()->create(['stripe_id' => 'cus_123']);
    $user = User::factory()->create();
    $workspace->users()->attach($user, ['role' => WorkspaceRole::Admin->value]);

    // Partial mock to bypass actual Stripe API call
    $workspaceMock = Mockery::mock($workspace)->makePartial();
    $workspaceMock->shouldReceive('billingPortalUrl')
        ->andReturn('https://billing.stripe.com/p/session/123');
    
    // Bind to container or mock app instance to use this mock for route binding if needed
    // Assuming we can test the API by binding it
    app()->instance(Workspace::class, $workspaceMock);

    $response = $this->actingAs($user)
        ->getJson("/api/v1/workspaces/{$workspace->id}/billing-portal");

    $response->assertOk()
        ->assertJsonPath('data.url', 'https://billing.stripe.com/p/session/123');
});
