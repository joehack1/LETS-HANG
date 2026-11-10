<?php

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function verifiedUser3(array $attrs = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
        'date_of_birth' => '1995-05-05',
        'country' => 'KE',
        'city' => 'Nairobi',
    ], $attrs));
}

it('creates a community and auto-joins owner', function () {
    $user = verifiedUser3();

    $res = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/communities', [
            'name' => 'Nairobi Developers',
            'visibility' => 'public',
        ]);

    $res->assertStatus(201);
    $community = Community::first();
    expect($community->members()->where('user_id', $user->id)->where('role', 'owner')->exists())->toBeTrue();
});

it('joins and posts in a public community', function () {
    $owner = verifiedUser3();
    $member = verifiedUser3();

    $res = $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/communities', ['name' => 'Test Community', 'visibility' => 'public']);
    $slug = $res->json('community.slug');

    $this->actingAs($member, 'sanctum')
        ->postJson("/api/v1/communities/{$slug}/join")
        ->assertOk();

    $this->actingAs($member, 'sanctum')
        ->postJson("/api/v1/communities/{$slug}/posts", ['content' => 'Hello community'])
        ->assertStatus(201);
});
