<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\JsonResponse;

class InterestController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'interests' => Interest::orderBy('name')->get(),
        ]);
    }
}
