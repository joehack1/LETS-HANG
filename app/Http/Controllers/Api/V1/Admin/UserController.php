<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('email', 'like', '%'.$request->string('search').'%');
            });
        }
        if ($request->filled('status')) {
            $query->where('account_status', $request->string('status'));
        }
        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        $users = $query->latest()->paginate(30);

        return response()->json([
            'data' => $users->getCollection()->map(fn (User $u) => [
                'id' => $u->id,
                'public_id' => $u->public_id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'account_status' => $u->account_status,
                'created_at' => $u->created_at,
            ]),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json(['user' => $user]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:active,suspended,banned'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = $request->user();
        $user = User::findOrFail($id);

        $user->update(['account_status' => $request->string('status')]);

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'user.status_changed',
            'target_type' => User::class,
            'target_id' => $user->id,
            'metadata' => ['status' => $request->string('status'), 'reason' => $request->input('reason')],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Status updated.']);
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        $request->validate(['role' => ['required', 'in:user,moderator,admin']]);

        $admin = $request->user();
        $user = User::findOrFail($id);

        if ($user->id === $admin->id) {
            return response()->json(['message' => 'You cannot change your own role.'], 422);
        }

        $user->update(['role' => $request->string('role')]);

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'user.role_changed',
            'target_type' => User::class,
            'target_id' => $user->id,
            'metadata' => ['role' => $request->string('role')],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Role updated.']);
    }
}
