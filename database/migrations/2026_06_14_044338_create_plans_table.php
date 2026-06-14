<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('stripe_price_id')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('plans')->insert([
            ['name' => 'Free', 'slug' => 'free', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pro', 'slug' => 'pro', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
