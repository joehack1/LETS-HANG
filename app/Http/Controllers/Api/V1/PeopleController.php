<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PeopleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $query = User::query()
            ->where('id', '!=', $authUser->id)
            ->where('account_status', 'active');

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->string('city').'%');
        }

        if ($request->filled('interest_id')) {
            $query->whereHas('interests', fn ($q) => $q->where('interests.id', $request->integer('interest_id')));
        }

        if ($request->filled('age_min')) {
            $query->whereDate('date_of_birth', '<=', now()->subYears($request->integer('age_min'))->toDateString());
        }
        if ($request->filled('age_max')) {
            $query->whereDate('date_of_birth', '>=', now()->subYears($request->integer('age_max') + 1)->toDateString());
        }

        if ($request->boolean('dating_only')) {
            $query->whereHas('preferences', fn ($q) => $q->where('dating_enabled', true));
        }

        $users = $query->with(['profile', 'interests', 'profilePhotos'])->paginate(20);

        $data = $users->getCollection()->map(function (User $user) {
            $profile = $user->profile ?: $user->ensureProfile();

            return [
                'public_id' => $user->public_id,
                'name' => $user->name,
                'display_name' => $profile->display_name ?? $user->name,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_path ? Storage::disk('public')->url($profile->avatar_path) : null,
                'city' => $user->city,
                'country' => $user->country,
                'age' => $user->age(),
                'interests' => $user->interests->map(fn ($i) => ['id' => $i->id, 'name' => $i->name]),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $user = User::where('public_id', $publicId)->firstOrFail();
        $profile = $user->ensureProfile();
        $preferences = $user->ensurePreferences();

        return response()->json([
            'profile' => [
                'public_id' => $user->public_id,
                'name' => $user->name,
                'display_name' => $profile->display_name ?? $user->name,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_path ? Storage::disk('public')->url($profile->avatar_path) : null,
                'city' => $user->city,
                'country' => $user->country,
                'age' => $user->age(),
                'interests' => $user->interests->map(fn ($i) => ['id' => $i->id, 'name' => $i->name]),
                'photos' => $user->profilePhotos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => Storage::disk('public')->url($p->path),
                ]),
            ],
        ]);
    }
}
