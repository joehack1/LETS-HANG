<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePreferencesRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Profile;
use App\Models\ProfilePhoto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->ensureProfile();
        $preferences = $user->ensurePreferences();

        return response()->json([
            'profile' => $this->profilePayload($user, $profile, $preferences),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->ensureProfile();

        $profile->fill($request->only(['display_name', 'bio', 'area', 'is_discoverable']));
        $profile->save();

        if ($request->has('interests')) {
            $user->interests()->sync($request->input('interests', []));
        }

        $preferences = $user->ensurePreferences();

        return response()->json([
            'profile' => $this->profilePayload($user, $profile->fresh(), $preferences),
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $profile = $user->ensureProfile();

        if ($profile->avatar_path) {
            Storage::disk('public')->delete($profile->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $profile->update(['avatar_path' => $path]);

        return response()->json(['avatar_url' => Storage::disk('public')->url($path)]);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user();
        $path = $request->file('photo')->store('profile-photos', 'public');

        $photo = ProfilePhoto::create([
            'user_id' => $user->id,
            'path' => $path,
            'sort_order' => $user->profilePhotos()->count(),
        ]);

        return response()->json([
            'photo' => [
                'id' => $photo->id,
                'url' => Storage::disk('public')->url($path),
            ],
        ], 201);
    }

    public function deletePhoto(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $photo = ProfilePhoto::where('user_id', $user->id)->findOrFail($id);
        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return response()->json(['message' => 'Photo deleted.']);
    }

    public function updatePreferences(UpdatePreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $user->ensurePreferences();
        $preferences->fill($request->validated());
        $preferences->save();

        return response()->json(['preferences' => $preferences->fresh()]);
    }

    public function publicProfile(string $publicId): JsonResponse
    {
        $user = User::where('public_id', $publicId)->firstOrFail();
        $profile = $user->ensureProfile();
        $preferences = $user->ensurePreferences();

        if (! $profile->is_discoverable || ! $preferences->discoverable) {
            return response()->json(['message' => 'Profile not available.'], 404);
        }

        return response()->json([
            'profile' => $this->publicPayload($user, $profile),
        ]);
    }

    private function profilePayload(User $user, Profile $profile, UserPreference $preferences): array
    {
        return [
            'public_id' => $user->public_id,
            'name' => $user->name,
            'display_name' => $profile->display_name ?? $user->name,
            'bio' => $profile->bio,
            'avatar_url' => $profile->avatar_path ? Storage::disk('public')->url($profile->avatar_path) : null,
            'area' => $profile->area ?? $user->city,
            'city' => $user->city,
            'country' => $user->country,
            'age' => $user->age(),
            'is_discoverable' => $profile->is_discoverable,
            'interests' => $user->interests()->get(['interests.id', 'interests.name', 'interests.slug']),
            'photos' => $user->profilePhotos()->get()->map(fn ($p) => [
                'id' => $p->id,
                'url' => Storage::disk('public')->url($p->path),
            ]),
            'preferences' => $preferences,
        ];
    }

    private function publicPayload(User $user, Profile $profile): array
    {
        return [
            'public_id' => $user->public_id,
            'name' => $user->name,
            'display_name' => $profile->display_name ?? $user->name,
            'bio' => $profile->bio,
            'avatar_url' => $profile->avatar_path ? Storage::disk('public')->url($profile->avatar_path) : null,
            'area' => $profile->area ?? $user->city,
            'city' => $user->city,
            'country' => $user->country,
            'age' => $user->age(),
            'interests' => $user->interests()->get(['interests.id', 'interests.name', 'interests.slug']),
            'photos' => $user->profilePhotos()->get()->map(fn ($p) => [
                'id' => $p->id,
                'url' => Storage::disk('public')->url($p->path),
            ]),
        ];
    }
}
