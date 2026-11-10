<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+254700000001',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'date_of_birth' => '1995-05-05',
        'country' => 'KE',
        'city' => 'Nairobi',
        'terms_accepted' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('user.email', 'test@example.com');

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

it('rejects underage registration', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Young User',
        'email' => 'young@example.com',
        'phone' => '+254700000002',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'date_of_birth' => now()->subYears(16)->format('Y-m-d'),
        'country' => 'KE',
        'city' => 'Nairobi',
        'terms_accepted' => true,
    ]);

    $response->assertStatus(422);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'dup@example.com']);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Dup User',
        'email' => 'dup@example.com',
        'phone' => '+254700000003',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'date_of_birth' => '1995-05-05',
        'country' => 'KE',
        'city' => 'Nairobi',
        'terms_accepted' => true,
    ]);

    $response->assertStatus(422);
});
