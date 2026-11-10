<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $communities = Community::withCount('members')->latest()->paginate(30);

        return response()->json([
            'data' => $communities->getCollection()->map(fn (Community $c) => [
                'slug' => $c->slug,
                'name' => $c->name,
                'visibility' => $c->visibility,
                'members_count' => $c->members_count,
                'created_at' => $c->created_at,
            ]),
            'meta' => [
                'current_page' => $communities->currentPage(),
                'last_page' => $communities->lastPage(),
                'total' => $communities->total(),
            ],
        ]);
    }

    public function suspend(Request $request, string $slug): JsonResponse
    {
        $community = Community::where('slug', $slug)->firstOrFail();
        $community->delete();

        return response()->json(['message' => 'Community suspended.']);
    }
}
