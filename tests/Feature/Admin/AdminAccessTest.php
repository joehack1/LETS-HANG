<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to access dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
        'date_of_birth' => '1995-05-05',
        'country' => 'KE',
        'city' => 'Nairobi',
    ]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/dashboard')
        ->assertOk();
});

it('forbids regular user from admin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
        'date_of_birth' => '1995-05-05',
        'country' => 'KE',
        'city' => 'Nairobi',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/admin/dashboard')
        ->assertStatus(403);
});
