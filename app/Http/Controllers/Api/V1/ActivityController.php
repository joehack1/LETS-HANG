<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\SavedActivity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Activity::query()
            ->with(['organizer', 'category', 'interests'])
            ->where('status', 'published');

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->string('city').'%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->string('search').'%');
        }
        if ($request->boolean('upcoming', true)) {
            $query->where('starts_at', '>=', now());
        }

        $activities = $query->orderBy('starts_at')->paginate(20);

        return response()->json([
            'data' => $activities->getCollection()->map(fn (Activity $a) => $this->activityPayload($a, $request->user())),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'total' => $activities->total(),
            ],
        ]);
    }

    public function store(StoreActivityRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isFullyVerified()) {
            return response()->json(['message' => 'You must verify your email and phone before creating activities.'], 403);
        }

        $activity = DB::transaction(function () use ($request, $user) {
            $activity = Activity::create(array_merge(
                $request->only([
                    'title', 'description', 'category_id', 'cover_image', 'starts_at', 'ends_at',
                    'country', 'city', 'area', 'venue_name', 'latitude', 'longitude',
                    'max_participants', 'joining_mode', 'visibility', 'price', 'currency', 'instructions',
                ]),
                ['organizer_id' => $user->id, 'status' => 'published']
            ));

            if ($request->has('interests')) {
                $activity->interests()->sync($request->input('interests', []));
            }

            // organizer auto-joined approved
            ActivityParticipant::create([
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'status' => 'approved',
                'joined_at' => now(),
            ]);

            return $activity;
        });

        $activity->load(['organizer', 'category', 'interests']);

        return response()->json([
            'activity' => $this->activityPayload($activity, $user),
        ], 201);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $activity = Activity::with(['organizer', 'category', 'interests', 'participants.user'])
            ->where('public_id', $publicId)
            ->firstOrFail();

        return response()->json([
            'activity' => $this->activityPayload($activity, $request->user(), true),
        ]);
    }

    public function update(UpdateActivityRequest $request, string $publicId): JsonResponse
    {
        $user = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        if ($activity->organizer_id !== $user->id && ! $user->isModerator()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        DB::transaction(function () use ($request, $activity) {
            $activity->fill($request->only([
                'title', 'description', 'category_id', 'cover_image', 'starts_at', 'ends_at',
                'country', 'city', 'area', 'venue_name', 'latitude', 'longitude',
                'max_participants', 'joining_mode', 'visibility', 'price', 'currency',
                'instructions', 'status',
            ]));
            $activity->save();

            if ($request->has('interests')) {
                $activity->interests()->sync($request->input('interests', []));
            }
        });

        $activity->load(['organizer', 'category', 'interests']);

        return response()->json(['activity' => $this->activityPayload($activity, $user)]);
    }

    public function destroy(Request $request, string $publicId): JsonResponse
    {
        $user = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        if ($activity->organizer_id !== $user->id && ! $user->isModerator()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $activity->delete();

        return response()->json(['message' => 'Activity deleted.']);
    }

    public function join(Request $request, string $publicId): JsonResponse
    {
        $user = $request->user();

        if (! $user->isFullyVerified()) {
            return response()->json(['message' => 'You must verify your email and phone before joining activities.'], 403);
        }

        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        if ($activity->status !== 'published') {
            return response()->json(['message' => 'Activity is not available.'], 422);
        }

        if ($user->isBlockedWith($activity->organizer_id)) {
            return response()->json(['message' => 'You cannot join this activity.'], 403);
        }

        $result = DB::transaction(function () use ($activity, $user) {
            $locked = Activity::lockForUpdate()->findOrFail($activity->id);

            $existing = ActivityParticipant::where('activity_id', $locked->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing && in_array($existing->status, ['pending', 'approved', 'waitlisted'], true)) {
                return ['status' => 'already', 'participant' => $existing];
            }

            $approvedCount = ActivityParticipant::where('activity_id', $locked->id)
                ->whereIn('status', ['approved', 'attended'])
                ->count();

            if ($locked->joining_mode === 'open') {
                if ($approvedCount >= $locked->max_participants) {
                    $status = 'waitlisted';
                } else {
                    $status = 'approved';
                }
            } else {
                $status = 'pending';
            }

            $participant = ActivityParticipant::updateOrCreate(
                ['activity_id' => $locked->id, 'user_id' => $user->id],
                ['status' => $status, 'joined_at' => now()]
            );

            return ['status' => $status, 'participant' => $participant];
        });

        return response()->json([
            'message' => $result['status'] === 'waitlisted'
                ? 'Activity is full. You have been waitlisted.'
                : ($result['status'] === 'already' ? 'Already joined.' : 'Joined successfully.'),
            'status' => $result['status'],
        ]);
    }

    public function leave(Request $request, string $publicId): JsonResponse
    {
        $user = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        DB::transaction(function () use ($activity, $user) {
            $participant = ActivityParticipant::where('activity_id', $activity->id)
                ->where('user_id', $user->id)
                ->first();

            if (! $participant) {
                return;
            }

            $wasApproved = in_array($participant->status, ['approved', 'attended'], true);
            $participant->update(['status' => 'cancelled']);

            if ($wasApproved) {
                // promote first waitlisted
                $next = ActivityParticipant::where('activity_id', $activity->id)
                    ->where('status', 'waitlisted')
                    ->orderBy('joined_at')
                    ->first();

                if ($next) {
                    $next->update(['status' => 'approved']);
                }
            }
        });

        return response()->json(['message' => 'Left activity.']);
    }

    public function approve(Request $request, string $publicId, int $userId): JsonResponse
    {
        return $this->reviewParticipant($request, $publicId, $userId, 'approved');
    }

    public function reject(Request $request, string $publicId, int $userId): JsonResponse
    {
        return $this->reviewParticipant($request, $publicId, $userId, 'rejected');
    }

    private function reviewParticipant(Request $request, string $publicId, int $userId, string $newStatus): JsonResponse
    {
        $user = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        if ($activity->organizer_id !== $user->id && ! $user->isModerator()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $participant = ActivityParticipant::where('activity_id', $activity->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($newStatus === 'approved') {
            $approvedCount = ActivityParticipant::where('activity_id', $activity->id)
                ->whereIn('status', ['approved', 'attended'])
                ->count();

            if ($approvedCount >= $activity->max_participants) {
                return response()->json(['message' => 'Activity is full.'], 422);
            }
        }

        $participant->update(['status' => $newStatus]);

        return response()->json(['message' => 'Participant '.$newStatus.'.']);
    }

    public function participants(Request $request, string $publicId): JsonResponse
    {
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        $participants = ActivityParticipant::where('activity_id', $activity->id)
            ->with('user')
            ->get();

        return response()->json([
            'data' => $participants->map(fn (ActivityParticipant $p) => [
                'id' => $p->id,
                'user' => [
                    'public_id' => $p->user->public_id,
                    'name' => $p->user->name,
                ],
                'status' => $p->status,
                'joined_at' => $p->joined_at,
            ]),
        ]);
    }

    public function save(Request $request, string $publicId): JsonResponse
    {
        $user = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        SavedActivity::firstOrCreate([
            'user_id' => $user->id,
            'activity_id' => $activity->id,
        ]);

        return response()->json(['message' => 'Activity saved.']);
    }

    public function unsave(Request $request, string $publicId): JsonResponse
    {
        $user = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        SavedActivity::where('user_id', $user->id)
            ->where('activity_id', $activity->id)
            ->delete();

        return response()->json(['message' => 'Activity unsaved.']);
    }

    public function markAttendance(Request $request, string $publicId, int $userId): JsonResponse
    {
        $user = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();

        if ($activity->organizer_id !== $user->id && ! $user->isModerator()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate(['status' => ['required', 'in:attended,no_show']]);

        $participant = ActivityParticipant::where('activity_id', $activity->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $participant->update(['status' => $request->string('status')]);

        return response()->json(['message' => 'Attendance updated.']);
    }

    private function activityPayload(Activity $activity, ?User $user = null, bool $detailed = false): array
    {
        $payload = [
            'public_id' => $activity->public_id,
            'title' => $activity->title,
            'description' => $activity->description,
            'cover_image' => $activity->cover_image,
            'cover_url' => $activity->cover_image ? Storage::disk('public')->url($activity->cover_image) : null,
            'category' => $activity->category?->name,
            'starts_at' => $activity->starts_at,
            'ends_at' => $activity->ends_at,
            'city' => $activity->city,
            'area' => $activity->area,
            'venue_name' => $activity->venue_name,
            'max_participants' => $activity->max_participants,
            'approved_count' => $activity->approvedCount(),
            'joining_mode' => $activity->joining_mode,
            'visibility' => $activity->visibility,
            'price' => $activity->price,
            'currency' => $activity->currency,
            'status' => $activity->status,
            'organizer' => [
                'public_id' => $activity->organizer->public_id,
                'name' => $activity->organizer->name,
            ],
            'interests' => $activity->interests->map(fn ($i) => ['id' => $i->id, 'name' => $i->name]),
        ];

        if ($detailed && $user) {
            $participant = $activity->participants->firstWhere('user_id', $user->id);
            $payload['my_status'] = $participant?->status;
            $payload['is_organizer'] = $activity->organizer_id === $user->id;
            $payload['instructions'] = $activity->instructions;
        }

        return $payload;
    }
}
