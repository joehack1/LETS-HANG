<?php

use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function verifiedUser2(array $attrs = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
        'date_of_birth' => '1995-05-05',
        'country' => 'KE',
        'city' => 'Nairobi',
    ], $attrs));
}

it('creates an activity and auto-joins organizer', function () {
    $user = verifiedUser2();
    $cat = ActivityCategory::create(['name' => 'Sports', 'slug' => 'sports']);

    $res = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/activities', [
            'title' => 'Football Match',
            'starts_at' => now()->addDays(2)->toDateTimeString(),
            'max_participants' => 10,
            'joining_mode' => 'open',
            'city' => 'Nairobi',
            'category_id' => $cat->id,
        ]);

    $res->assertStatus(201);
    $activity = Activity::first();
    expect($activity->participants()->where('user_id', $user->id)->where('status', 'approved')->exists())->toBeTrue();
});

it('joins an open activity', function () {
    $organizer = verifiedUser2();
    $joiner = verifiedUser2();

    $cat = ActivityCategory::create(['name' => 'Sports', 'slug' => 'sports']);

    $res = $this->actingAs($organizer, 'sanctum')
        ->postJson('/api/v1/activities', [
            'title' => 'Football Match',
            'starts_at' => now()->addDays(2)->toDateTimeString(),
            'max_participants' => 10,
            'joining_mode' => 'open',
            'city' => 'Nairobi',
            'category_id' => $cat->id,
        ]);
    $publicId = $res->json('activity.public_id');

    $this->actingAs($joiner, 'sanctum')
        ->postJson("/api/v1/activities/{$publicId}/join")
        ->assertOk();

    $activity = Activity::where('public_id', $publicId)->first();
    expect($activity->participants()->where('user_id', $joiner->id)->where('status', 'approved')->exists())->toBeTrue();
});

it('waitlists when full', function () {
    $organizer = verifiedUser2();
    $joiner = verifiedUser2();

    $cat = ActivityCategory::create(['name' => 'Sports', 'slug' => 'sports']);

    $res = $this->actingAs($organizer, 'sanctum')
        ->postJson('/api/v1/activities', [
            'title' => 'Small Meetup',
            'starts_at' => now()->addDays(2)->toDateTimeString(),
            'max_participants' => 2,
            'joining_mode' => 'open',
            'city' => 'Nairobi',
            'category_id' => $cat->id,
        ]);
    $publicId = $res->json('activity.public_id');

    // organizer + one more fills capacity (max 2)
    $other = verifiedUser2();
    $this->actingAs($other, 'sanctum')->postJson("/api/v1/activities/{$publicId}/join")->assertOk();

    $this->actingAs($joiner, 'sanctum')
        ->postJson("/api/v1/activities/{$publicId}/join")
        ->assertOk()
        ->assertJsonPath('status', 'waitlisted');
});

it('requires verification to join', function () {
    $organizer = verifiedUser2();
    $unverified = User::factory()->create(['date_of_birth' => '1995-05-05', 'country' => 'KE', 'city' => 'Nairobi']);

    $cat = ActivityCategory::create(['name' => 'Sports', 'slug' => 'sports']);
    $res = $this->actingAs($organizer, 'sanctum')
        ->postJson('/api/v1/activities', [
            'title' => 'Football Match',
            'starts_at' => now()->addDays(2)->toDateTimeString(),
            'max_participants' => 10,
            'joining_mode' => 'open',
            'city' => 'Nairobi',
            'category_id' => $cat->id,
        ]);
    $publicId = $res->json('activity.public_id');

    $this->actingAs($unverified, 'sanctum')
        ->postJson("/api/v1/activities/{$publicId}/join")
        ->assertStatus(403);
});
