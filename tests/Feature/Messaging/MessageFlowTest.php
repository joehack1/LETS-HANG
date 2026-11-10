<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a direct conversation and sends a message', function () {
    $a = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);
    $b = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);

    $res = $this->actingAs($a, 'sanctum')
        ->postJson('/api/v1/conversations', [
            'type' => 'direct',
            'participants' => [$b->public_id],
        ]);
    $res->assertStatus(201);
    $convId = $res->json('conversation.id');

    $this->actingAs($a, 'sanctum')
        ->postJson("/api/v1/conversations/{$convId}/messages", ['body' => 'Hello'])
        ->assertStatus(201);

    $this->actingAs($b, 'sanctum')
        ->getJson("/api/v1/conversations/{$convId}/messages")
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('prevents non-participants from reading messages', function () {
    $a = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);
    $b = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);
    $c = User::factory()->create(['email_verified_at' => now(), 'phone_verified_at' => now(), 'date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);

    $res = $this->actingAs($a, 'sanctum')
        ->postJson('/api/v1/conversations', ['type' => 'direct', 'participants' => [$b->public_id]]);
    $convId = $res->json('conversation.id');

    $this->actingAs($c, 'sanctum')
        ->getJson("/api/v1/conversations/{$convId}/messages")
        ->assertStatus(403);
});
