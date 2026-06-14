<?php

use App\Models\Plan;

it('has free and pro tiers with feature flags', function () {
    $freePlan = Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'features' => ['max_members' => 3],
    ]);

    $proPlan = Plan::factory()->create([
        'name' => 'Pro',
        'slug' => 'pro',
        'features' => ['max_members' => -1],
    ]);

    expect($freePlan->features['max_members'])->toBe(3)
        ->and($proPlan->features['max_members'])->toBe(-1);
});
