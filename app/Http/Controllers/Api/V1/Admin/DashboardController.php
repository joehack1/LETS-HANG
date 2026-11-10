<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Community;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'verified_users' => User::whereNotNull('email_verified_at')->whereNotNull('phone_verified_at')->count(),
                'suspended_users' => User::where('account_status', 'suspended')->count(),
                'total_activities' => Activity::count(),
                'upcoming_activities' => Activity::where('status', 'published')->where('starts_at', '>=', now())->count(),
                'total_communities' => Community::count(),
                'pending_reports' => Report::where('status', 'pending')->count(),
            ],
        ]);
    }
}
