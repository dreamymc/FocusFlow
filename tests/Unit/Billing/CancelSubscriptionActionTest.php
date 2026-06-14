<?php

use App\Models\Workspace;
use App\Actions\Billing\CancelSubscriptionAction;

it('cancels a workspace subscription', function () {
    $action = app(CancelSubscriptionAction::class);
    
    $subscriptionMock = Mockery::mock();
    $subscriptionMock->shouldReceive('cancel')->once()->andReturnTrue();

    $workspaceMock = Mockery::mock(Workspace::class)->makePartial();
    $workspaceMock->shouldReceive('subscription')
        ->with('default')
        ->once()
        ->andReturn($subscriptionMock);

    $action->execute($workspaceMock);
});
