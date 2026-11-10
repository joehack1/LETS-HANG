<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'public_id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'account_status' => $user->account_status,
                'email_verified' => $user->email_verified_at !== null,
                'phone_verified' => $user->phone_verified_at !== null,
                'fully_verified' => $user->isFullyVerified(),
                'age' => $user->age(),
                'country' => $user->country,
                'city' => $user->city,
            ],
        ]);
    }
}
