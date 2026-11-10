<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:user,activity,community,post'],
            'target' => ['required', 'string'],
            'reason' => ['required', 'string', 'max:60'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        [$type, $model] = $this->resolveTarget($request->string('type')->toString(), $request->string('target')->toString());

        if (! $model) {
            return response()->json(['message' => 'Target not found.'], 404);
        }

        if ($type === User::class && $model->id === $user->id) {
            return response()->json(['message' => 'You cannot report yourself.'], 422);
        }

        $report = Report::create([
            'reporter_id' => $user->id,
            'reportable_type' => $type,
            'reportable_id' => $model->id,
            'reason' => $request->string('reason')->toString(),
            'details' => $request->input('details'),
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Report submitted.', 'report' => $report], 201);
    }

    private function resolveTarget(string $type, string $target): array
    {
        return match ($type) {
            'user' => [User::class, User::where('public_id', $target)->first()],
            'activity' => [Activity::class, Activity::where('public_id', $target)->first()],
            'community' => [Community::class, Community::where('slug', $target)->first()],
            'post' => [CommunityPost::class, CommunityPost::find($target)],
            default => [null, null],
        };
    }
}
