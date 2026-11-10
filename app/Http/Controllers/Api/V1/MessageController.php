<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $conversation = Conversation::findOrFail($conversationId);

        if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->latest()
            ->paginate(50);

        return response()->json([
            'data' => $messages->getCollection()->reverse()->values()->map(fn (Message $m) => [
                'id' => $m->id,
                'body' => $m->body,
                'type' => $m->type,
                'attachment_url' => $m->attachment_path ? Storage::disk('public')->url($m->attachment_path) : null,
                'sender' => [
                    'public_id' => $m->sender->public_id,
                    'name' => $m->sender->name,
                ],
                'created_at' => $m->created_at,
            ]),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
            ],
        ]);
    }

    public function store(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user();
        $conversation = Conversation::findOrFail($conversationId);

        if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $body = $request->input('body');
        $attachmentPath = null;
        $type = 'text';

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('message-attachments', 'public');
            $type = 'image';
        }

        if (empty($body) && empty($attachmentPath)) {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'body' => $body,
            'attachment_path' => $attachmentPath,
            'type' => $type,
        ]);

        $conversation->touch();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'type' => $message->type,
                'attachment_url' => $attachmentPath ? Storage::disk('public')->url($attachmentPath) : null,
                'sender' => ['public_id' => $user->public_id, 'name' => $user->name],
                'created_at' => $message->created_at,
            ],
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $message = Message::findOrFail($id);

        if ($message->sender_id !== $user->id && ! $user->isModerator()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted.']);
    }
}
