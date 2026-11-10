<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Activity::with(['organizer', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->string('city').'%');
        }

        $activities = $query->latest()->paginate(30);

        return response()->json([
            'data' => $activities->getCollection()->map(fn (Activity $a) => [
                'public_id' => $a->public_id,
                'title' => $a->title,
                'status' => $a->status,
                'city' => $a->city,
                'starts_at' => $a->starts_at,
                'organizer' => $a->organizer->name,
            ]),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'total' => $activities->total(),
            ],
        ]);
    }

    public function updateStatus(Request $request, string $publicId): JsonResponse
    {
        $request->validate(['status' => ['required', 'in:published,cancelled,completed']]);

        $admin = $request->user();
        $activity = Activity::where('public_id', $publicId)->firstOrFail();
        $activity->update(['status' => $request->string('status')]);

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'activity.status_changed',
            'target_type' => Activity::class,
            'target_id' => $activity->id,
            'metadata' => ['status' => $request->string('status')],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Activity status updated.']);
    }
}
