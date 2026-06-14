<?php

use App\Models\Workspace;
use App\Actions\Billing\SubscribeWorkspaceAction;

it('subscribes a workspace to a plan', function () {
    $action = app(SubscribeWorkspaceAction::class);
    
    $subscriptionMock = Mockery::mock();
    $subscriptionMock->shouldReceive('create')
        ->with('pm_123')
        ->once()
        ->andReturn((object)['stripe_id' => 'sub_123']);
    
    $workspaceMock = Mockery::mock(Workspace::class)->makePartial();
    $workspaceMock->shouldReceive('newSubscription')
        ->with('default', 'price_pro')
        ->once()
        ->andReturn($subscriptionMock);

    // Execute the action with mocked workspace
    $result = $action->execute($workspaceMock, 'pm_123', 'price_pro');
    
    expect($result->stripe_id)->toBe('sub_123');
});
