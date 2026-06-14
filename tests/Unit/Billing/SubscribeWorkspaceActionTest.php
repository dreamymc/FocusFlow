<?php

use App\Models\Workspace;
use App\Actions\SubscribeWorkspaceAction;

it('subscribes a workspace to a plan', function () {
    $action = app(SubscribeWorkspaceAction::class);
    
    $builderMock = Mockery::mock(\Laravel\Cashier\SubscriptionBuilder::class);
    $builderMock->shouldReceive('create')
        ->with('pm_123')
        ->once()
        ->andReturn(Mockery::mock(\Laravel\Cashier\Subscription::class, function ($mock) {
            $mock->shouldReceive('getAttribute')->with('stripe_id')->andReturn('sub_123');
        }));
    
    $workspaceMock = Mockery::mock(Workspace::class)->makePartial();
    $workspaceMock->shouldReceive('newSubscription')
        ->with('default', 'price_pro')
        ->once()
        ->andReturn($builderMock);

    // Execute the action with mocked workspace
    $result = $action->execute($workspaceMock, 'pm_123', 'price_pro');
    
    expect($result->stripe_id)->toBe('sub_123');
});
