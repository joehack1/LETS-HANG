<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('submits a report', function () {
    $reporter = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);
    $target = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);

    $this->actingAs($reporter, 'sanctum')
        ->postJson('/api/v1/reports', [
            'type' => 'user',
            'target' => $target->public_id,
            'reason' => 'harassment',
        ])
        ->assertStatus(201);
});

it('lists notifications', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notifications')
        ->assertOk();
});
