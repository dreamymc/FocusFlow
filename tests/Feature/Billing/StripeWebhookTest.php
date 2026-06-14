<?php

use App\Models\Workspace;
use Illuminate\Support\Facades\Http;

it('activates pro plan on successful payment', function () {
    Http::fake(['https://api.stripe.com/*' => Http::response(['status' => 'active'])]);

    $workspace = Workspace::factory()->create(['stripe_id' => 'cus_123']);

    $response = $this->postJson('/stripe/webhook', [
        'type' => 'invoice.paid',
        'data' => [
            'object' => [
                'customer' => $workspace->stripe_id,
                'subscription' => 'sub_123',
                'lines' => [
                    'data' => [
                        ['plan' => ['id' => 'price_pro']],
                    ],
                ],
            ],
        ],
    ]);

    $response->assertOk();
});

it('handles customer subscription deleted', function () {
    Http::fake(['https://api.stripe.com/*' => Http::response(['status' => 'canceled'])]);

    $workspace = Workspace::factory()->create(['stripe_id' => 'cus_123']);
    
    $workspace->subscriptions()->create([
        'name' => 'default',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $response = $this->postJson('/stripe/webhook', [
        'type' => 'customer.subscription.deleted',
        'data' => [
            'object' => [
                'customer' => $workspace->stripe_id,
                'id' => 'sub_123',
            ],
        ],
    ]);

    $response->assertOk();
    
    $this->assertDatabaseHas('subscriptions', [
        'stripe_id' => 'sub_123',
        'stripe_status' => 'canceled',
    ]);
});
