<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['reporter', 'resolver']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $reports = $query->latest()->paginate(30);

        return response()->json([
            'data' => $reports->getCollection()->map(fn (Report $r) => [
                'id' => $r->id,
                'reporter' => $r->reporter->name,
                'reportable_type' => $r->reportable_type,
                'reportable_id' => $r->reportable_id,
                'reason' => $r->reason,
                'details' => $r->details,
                'status' => $r->status,
                'created_at' => $r->created_at,
            ]),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:resolved,dismissed'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = $request->user();
        $report = Report::findOrFail($id);

        $report->update([
            'status' => $request->string('status'),
            'resolver_id' => $admin->id,
            'resolution_note' => $request->input('note'),
        ]);

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'report.'.$request->string('status'),
            'target_type' => Report::class,
            'target_id' => $report->id,
            'metadata' => ['note' => $request->input('note')],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Report updated.']);
    }
}
