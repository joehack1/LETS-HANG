<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $blocks = Block::where('blocker_id', $user->id)->with('blocked')->get();

        return response()->json([
            'data' => $blocks->map(fn (Block $b) => [
                'id' => $b->id,
                'user' => [
                    'public_id' => $b->blocked->public_id,
                    'name' => $b->blocked->name,
                ],
                'created_at' => $b->created_at,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_public_id' => ['required', 'string'],
        ]);

        $blocker = $request->user();
        $blocked = User::where('public_id', $request->string('user_public_id'))->firstOrFail();

        if ($blocker->id === $blocked->id) {
            return response()->json(['message' => 'You cannot block yourself.'], 422);
        }

        Block::firstOrCreate([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        return response()->json(['message' => 'User blocked.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $block = Block::where('blocker_id', $user->id)->findOrFail($id);
        $block->delete();

        return response()->json(['message' => 'User unblocked.']);
    }
}
