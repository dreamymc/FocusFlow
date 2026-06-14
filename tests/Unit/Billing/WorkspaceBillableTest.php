<?php

use App\Models\Workspace;

it('uses the billable trait', function () {
    $workspace = new Workspace();

    expect(method_exists($workspace, 'charge'))->toBeTrue()
        ->and(method_exists($workspace, 'subscribed'))->toBeTrue();
});
