<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ConnectionRequest;
use App\Models\Friendship;
use App\Models\User;
use App\Notifications\ConnectionAccepted;
use App\Notifications\ConnectionRequestReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConnectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->string('type', 'friendship')->toString();
        $direction = $request->string('direction', 'incoming')->toString();

        $query = ConnectionRequest::query()->with(['sender', 'receiver']);

        if ($direction === 'outgoing') {
            $query->where('sender_id', $user->id);
        } else {
            $query->where('receiver_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('intention')) {
            $query->where('intention', $request->string('intention'));
        }

        $requests = $query->latest()->paginate(20);

        return response()->json([
            'data' => $requests->getCollection()->map(fn (ConnectionRequest $r) => $this->requestPayload($r)),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_public_id' => ['required', 'string'],
            'intention' => ['required', 'in:friendship,dating'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $sender = $request->user();

        if (! $sender->isFullyVerified()) {
            return response()->json(['message' => 'You must verify your email and phone before sending connection requests.'], 403);
        }

        $receiver = User::where('public_id', $request->string('receiver_public_id'))->firstOrFail();

        if ($sender->id === $receiver->id) {
            return response()->json(['message' => 'You cannot connect with yourself.'], 422);
        }

        if ($sender->isBlockedWith($receiver->id)) {
            return response()->json(['message' => 'You cannot interact with this user.'], 403);
        }

        $intention = $request->string('intention')->toString();

        if ($intention === 'dating') {
            if (! ($sender->preferences?->dating_enabled ?? false) || ! ($receiver->preferences?->dating_enabled ?? false)) {
                return response()->json(['message' => 'Dating connection requires both users to have dating enabled.'], 422);
            }
        } else {
            if (! ($sender->preferences?->friendship_enabled ?? true) || ! ($receiver->preferences?->friendship_enabled ?? true)) {
                return response()->json(['message' => 'Friendship connections are disabled for one of the users.'], 422);
            }
        }

        // Already friends?
        [$low, $high] = Friendship::canonicalIds($sender->id, $receiver->id);
        if (Friendship::where('user_low_id', $low)->where('user_high_id', $high)->where('type', $intention)->exists()) {
            return response()->json(['message' => 'You are already connected.'], 422);
        }

        // Existing pending request?
        $existing = ConnectionRequest::where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->where('intention', $intention)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'A pending request already exists.'], 422);
        }

        // Reverse pending?
        $reverse = ConnectionRequest::where('sender_id', $receiver->id)
            ->where('receiver_id', $sender->id)
            ->where('intention', $intention)
            ->where('status', 'pending')
            ->first();

        if ($reverse) {
            return response()->json(['message' => 'This user has already sent you a request. Please respond to it.'], 422);
        }

        $connectionRequest = ConnectionRequest::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'intention' => $intention,
            'status' => 'pending',
            'message' => $request->input('message'),
        ]);

        $receiver->notify(new ConnectionRequestReceived($connectionRequest));

        return response()->json([
            'message' => 'Connection request sent.',
            'request' => $this->requestPayload($connectionRequest->fresh(['sender', 'receiver'])),
        ], 201);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $cr = ConnectionRequest::where('receiver_id', $user->id)->findOrFail($id);

        if ($cr->status !== 'pending') {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        if ($user->isBlockedWith($cr->sender_id)) {
            return response()->json(['message' => 'You cannot interact with this user.'], 403);
        }

        if ($cr->intention === 'dating') {
            if (! ($user->preferences?->dating_enabled ?? false) || ! ($cr->sender->preferences?->dating_enabled ?? false)) {
                return response()->json(['message' => 'Dating connection requires both users to have dating enabled.'], 422);
            }
        }

        $cr->update(['status' => 'accepted']);

        $cr->sender->notify(new ConnectionAccepted($cr));

        [$low, $high] = Friendship::canonicalIds($cr->sender_id, $cr->receiver_id);
        Friendship::firstOrCreate([
            'user_low_id' => $low,
            'user_high_id' => $high,
            'type' => $cr->intention,
        ]);

        return response()->json(['message' => 'Connection accepted.']);
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $cr = ConnectionRequest::where('receiver_id', $user->id)->findOrFail($id);

        if ($cr->status !== 'pending') {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        $cr->update(['status' => 'declined']);

        return response()->json(['message' => 'Connection declined.']);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $cr = ConnectionRequest::where('sender_id', $user->id)->findOrFail($id);

        if ($cr->status !== 'pending') {
            return response()->json(['message' => 'Request is no longer pending.'], 422);
        }

        $cr->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Request cancelled.']);
    }

    public function remove(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $friendship = Friendship::findOrFail($id);

        if ($friendship->user_low_id !== $user->id && $friendship->user_high_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $friendship->delete();

        return response()->json(['message' => 'Connection removed.']);
    }

    public function listConnections(Request $request): JsonResponse
    {
        $user = $request->user();

        $friendships = Friendship::where('user_low_id', $user->id)
            ->orWhere('user_high_id', $user->id)
            ->with(['lowUser.profile', 'highUser.profile'])
            ->get();

        $data = $friendships->map(function (Friendship $f) use ($user) {
            $other = $f->user_low_id === $user->id ? $f->highUser : $f->lowUser;
            $profile = $other->profile ?: $other->ensureProfile();

            return [
                'id' => $f->id,
                'type' => $f->type,
                'user' => [
                    'public_id' => $other->public_id,
                    'name' => $other->name,
                    'display_name' => $profile->display_name ?? $other->name,
                    'avatar_url' => $profile->avatar_path ? Storage::disk('public')->url($profile->avatar_path) : null,
                    'city' => $other->city,
                ],
            ];
        });

        return response()->json(['data' => $data]);
    }

    private function requestPayload(ConnectionRequest $r): array
    {
        return [
            'id' => $r->id,
            'intention' => $r->intention,
            'status' => $r->status,
            'message' => $r->message,
            'sender' => [
                'public_id' => $r->sender->public_id,
                'name' => $r->sender->name,
            ],
            'receiver' => [
                'public_id' => $r->receiver->public_id,
                'name' => $r->receiver->name,
            ],
            'created_at' => $r->created_at,
        ];
    }
}
