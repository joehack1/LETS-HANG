<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommunityRequest;
use App\Http\Requests\Community\UpdateCommunityRequest;
use App\Models\Activity;
use App\Models\Community;
use App\Models\CommunityComment;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Community::query()->withCount('members');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        // show public + communities user is member of
        $query->where(function ($q) use ($user) {
            $q->where('visibility', 'public')
                ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'));
        });

        $communities = $query->orderBy('name')->paginate(20);

        return response()->json([
            'data' => $communities->getCollection()->map(fn (Community $c) => $this->communityPayload($c, $user)),
            'meta' => [
                'current_page' => $communities->currentPage(),
                'last_page' => $communities->lastPage(),
                'total' => $communities->total(),
            ],
        ]);
    }

    public function store(StoreCommunityRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isFullyVerified()) {
            return response()->json(['message' => 'You must verify your email and phone before creating communities.'], 403);
        }

        $community = Community::create(array_merge(
            $request->validated(),
            ['owner_id' => $user->id]
        ));

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return response()->json(['community' => $this->communityPayload($community->fresh(), $user)], 201);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if ($community->visibility === 'private' && ! $this->isActiveMember($community, $user)) {
            return response()->json(['message' => 'Community not found.'], 404);
        }

        return response()->json(['community' => $this->communityPayload($community, $user)]);
    }

    public function update(UpdateCommunityRequest $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if (! $this->canManage($community, $user)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $community->fill($request->validated());
        $community->save();

        return response()->json(['community' => $this->communityPayload($community->fresh(), $user)]);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if ($community->owner_id !== $user->id && ! $user->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $community->delete();

        return response()->json(['message' => 'Community deleted.']);
    }

    public function join(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if ($community->visibility === 'private') {
            $member = CommunityMember::firstOrCreate(
                ['community_id' => $community->id, 'user_id' => $user->id],
                ['role' => 'member', 'status' => 'pending', 'joined_at' => now()]
            );

            return response()->json(['message' => 'Join request submitted.', 'status' => $member->status]);
        }

        $member = CommunityMember::firstOrCreate(
            ['community_id' => $community->id, 'user_id' => $user->id],
            ['role' => 'member', 'status' => 'active', 'joined_at' => now()]
        );

        return response()->json(['message' => 'Joined community.', 'status' => $member->status]);
    }

    public function leave(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if ($community->owner_id === $user->id) {
            return response()->json(['message' => 'Owner cannot leave. Transfer ownership first.'], 422);
        }

        CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Left community.']);
    }

    public function members(Request $request, string $slug): JsonResponse
    {
        $community = Community::where('slug', $slug)->firstOrFail();

        $members = CommunityMember::where('community_id', $community->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        return response()->json([
            'data' => $members->map(fn (CommunityMember $m) => [
                'id' => $m->id,
                'role' => $m->role,
                'status' => $m->status,
                'user' => [
                    'public_id' => $m->user->public_id,
                    'name' => $m->user->name,
                ],
                'joined_at' => $m->joined_at,
            ]),
        ]);
    }

    public function approveMember(Request $request, string $slug, int $userId): JsonResponse
    {
        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if (! $this->canManage($community, $user)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $member->update(['status' => 'active']);

        return response()->json(['message' => 'Member approved.']);
    }

    public function updateMemberRole(Request $request, string $slug, int $userId): JsonResponse
    {
        $request->validate(['role' => ['required', 'in:moderator,member']]);

        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if ($community->owner_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $member->update(['role' => $request->string('role')]);

        return response()->json(['message' => 'Role updated.']);
    }

    public function posts(Request $request, string $slug): JsonResponse
    {
        $community = Community::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        if ($community->visibility === 'private' && ! $this->isActiveMember($community, $user)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $posts = CommunityPost::where('community_id', $community->id)
            ->with(['user', 'comments.user'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $posts->getCollection()->map(fn (CommunityPost $p) => [
                'id' => $p->id,
                'content' => $p->content,
                'image_url' => $p->image_path ? Storage::disk('public')->url($p->image_path) : null,
                'author' => [
                    'public_id' => $p->user->public_id,
                    'name' => $p->user->name,
                ],
                'comments' => $p->comments->map(fn ($c) => [
                    'id' => $c->id,
                    'content' => $c->content,
                    'author' => $c->user->name,
                    'created_at' => $c->created_at,
                ]),
                'created_at' => $p->created_at,
            ]),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function createPost(Request $request, string $slug): JsonResponse
    {
        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();

        if (! $this->isActiveMember($community, $user)) {
            return response()->json(['message' => 'You must be a member to post.'], 403);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('community-posts', 'public');
        }

        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'content' => $request->string('content'),
            'image_path' => $imagePath,
        ]);

        return response()->json(['post' => $post], 201);
    }

    public function deletePost(Request $request, string $slug, int $postId): JsonResponse
    {
        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();
        $post = CommunityPost::where('community_id', $community->id)->findOrFail($postId);

        if ($post->user_id !== $user->id && ! $this->canManage($community, $user)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }

    public function createComment(Request $request, string $slug, int $postId): JsonResponse
    {
        $request->validate(['content' => ['required', 'string', 'max:2000']]);

        $user = $request->user();
        $community = Community::where('slug', $slug)->firstOrFail();
        $post = CommunityPost::where('community_id', $community->id)->findOrFail($postId);

        if (! $this->isActiveMember($community, $user)) {
            return response()->json(['message' => 'You must be a member to comment.'], 403);
        }

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $request->string('content'),
        ]);

        return response()->json(['comment' => $comment], 201);
    }

    public function communityActivities(Request $request, string $slug): JsonResponse
    {
        $community = Community::where('slug', $slug)->firstOrFail();

        $activities = Activity::where('community_id', $community->id)
            ->where('status', 'published')
            ->with(['organizer', 'category'])
            ->orderBy('starts_at')
            ->get();

        return response()->json(['data' => $activities->map(fn ($a) => [
            'public_id' => $a->public_id,
            'title' => $a->title,
            'starts_at' => $a->starts_at,
        ])]);
    }

    private function isActiveMember(Community $community, User $user): bool
    {
        return CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    private function canManage(Community $community, User $user): bool
    {
        if ($community->owner_id === $user->id || $user->isAdmin()) {
            return true;
        }

        return CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'moderator'])
            ->exists();
    }

    private function communityPayload(Community $community, User $user): array
    {
        $membership = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        return [
            'id' => $community->id,
            'slug' => $community->slug,
            'name' => $community->name,
            'description' => $community->description,
            'cover_image' => $community->cover_image,
            'cover_url' => $community->cover_image ? Storage::disk('public')->url($community->cover_image) : null,
            'category' => $community->category,
            'visibility' => $community->visibility,
            'owner_id' => $community->owner_id,
            'members_count' => $community->members_count ?? $community->members()->where('status', 'active')->count(),
            'my_role' => $membership?->role,
            'my_status' => $membership?->status,
            'is_member' => $membership?->status === 'active',
        ];
    }
}
