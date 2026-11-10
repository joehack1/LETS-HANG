<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Plan::where('is_active', true)->get(),
        ]);
    }
}
