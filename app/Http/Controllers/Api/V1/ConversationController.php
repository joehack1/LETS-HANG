<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = $user->conversations()
            ->with(['participants', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->get();

        $data = $conversations->map(function (Conversation $c) use ($user) {
            $lastMessage = $c->messages->first();
            $participant = $c->participants->firstWhere('id', $user->id);
            $lastRead = $participant?->pivot?->last_read_at;

            $unread = 0;
            if ($lastRead) {
                $unread = $c->messages()->where('created_at', '>', $lastRead)->where('sender_id', '!=', $user->id)->count();
            } else {
                $unread = $c->messages()->where('sender_id', '!=', $user->id)->count();
            }

            $otherParticipants = $c->participants->where('id', '!=', $user->id)->values();

            return [
                'id' => $c->id,
                'type' => $c->type,
                'title' => $c->title,
                'participants' => $otherParticipants->map(fn ($p) => [
                    'public_id' => $p->public_id,
                    'name' => $p->name,
                ]),
                'last_message' => $lastMessage ? [
                    'body' => $lastMessage->body,
                    'type' => $lastMessage->type,
                    'created_at' => $lastMessage->created_at,
                    'sender_id' => $lastMessage->sender_id,
                ] : null,
                'unread_count' => $unread,
                'updated_at' => $c->updated_at,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['sometimes', 'in:direct,group'],
            'title' => ['nullable', 'string', 'max:120'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['string'], // public_ids
        ]);

        $user = $request->user();
        $type = $request->string('type', 'direct')->toString();

        $participantIds = User::whereIn('public_id', $request->input('participants'))
            ->pluck('id')
            ->toArray();

        if (empty($participantIds)) {
            return response()->json(['message' => 'Participants not found.'], 422);
        }

        // For direct, find existing
        if ($type === 'direct' && count($participantIds) === 1) {
            $otherId = $participantIds[0];
            $existing = Conversation::where('type', 'direct')
                ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
                ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
                ->first();

            if ($existing) {
                return response()->json(['conversation' => ['id' => $existing->id]]);
            }
        }

        $conversation = DB::transaction(function () use ($type, $request, $user, $participantIds) {
            $conversation = Conversation::create([
                'type' => $type,
                'title' => $request->input('title'),
            ]);

            $allIds = array_unique(array_merge([$user->id], $participantIds));
            foreach ($allIds as $id) {
                $conversation->participants()->attach($id, ['last_read_at' => null]);
            }

            return $conversation;
        });

        return response()->json(['conversation' => ['id' => $conversation->id]], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $conversation = Conversation::with('participants')->findOrFail($id);

        if (! $conversation->participants->contains('id', $user->id)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'title' => $conversation->title,
                'participants' => $conversation->participants->map(fn ($p) => [
                    'public_id' => $p->public_id,
                    'name' => $p->name,
                ]),
            ],
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $conversation = Conversation::findOrFail($id);

        if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        DB::table('conversation_participants')
            ->where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }
}
