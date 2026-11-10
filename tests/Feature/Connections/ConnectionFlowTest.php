<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function verifiedUser(array $attrs = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
        'date_of_birth' => '1995-05-05',
        'country' => 'KE',
        'city' => 'Nairobi',
    ], $attrs));
}

it('requires verification to send connection request', function () {
    $sender = User::factory()->create(['date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);
    $receiver = verifiedUser();

    $this->actingAs($sender, 'sanctum')
        ->postJson('/api/v1/connections/requests', [
            'receiver_public_id' => $receiver->public_id,
            'intention' => 'friendship',
        ])
        ->assertStatus(403);
});

it('sends and accepts a friendship request', function () {
    $sender = verifiedUser();
    $receiver = verifiedUser();

    $res = $this->actingAs($sender, 'sanctum')
        ->postJson('/api/v1/connections/requests', [
            'receiver_public_id' => $receiver->public_id,
            'intention' => 'friendship',
        ]);
    $res->assertStatus(201);
    $requestId = $res->json('request.id');

    $this->actingAs($receiver, 'sanctum')
        ->postJson("/api/v1/connections/requests/{$requestId}/accept")
        ->assertOk();

    $this->actingAs($sender, 'sanctum')
        ->getJson('/api/v1/connections')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('prevents duplicate connection requests', function () {
    $sender = verifiedUser();
    $receiver = verifiedUser();

    $this->actingAs($sender, 'sanctum')
        ->postJson('/api/v1/connections/requests', [
            'receiver_public_id' => $receiver->public_id,
            'intention' => 'friendship',
        ])->assertStatus(201);

    $this->actingAs($sender, 'sanctum')
        ->postJson('/api/v1/connections/requests', [
            'receiver_public_id' => $receiver->public_id,
            'intention' => 'friendship',
        ])->assertStatus(422);
});

it('blocks interaction between blocked users', function () {
    $sender = verifiedUser();
    $receiver = verifiedUser();

    $this->actingAs($sender, 'sanctum')
        ->postJson('/api/v1/blocks', ['user_public_id' => $receiver->public_id])
        ->assertOk();

    $this->actingAs($sender, 'sanctum')
        ->postJson('/api/v1/connections/requests', [
            'receiver_public_id' => $receiver->public_id,
            'intention' => 'friendship',
        ])->assertStatus(403);
});
