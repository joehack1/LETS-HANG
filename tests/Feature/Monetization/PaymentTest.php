<?php

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists plans', function () {
    Plan::create(['name' => 'Free', 'slug' => 'free', 'price' => 0, 'currency' => 'KES', 'interval' => 'monthly', 'is_active' => true]);

    $user = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/plans')
        ->assertOk();
});

it('returns subscription status', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/subscription/status')
        ->assertOk()
        ->assertJsonPath('is_premium', false);
});
