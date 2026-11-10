<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->account_status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $this->userPayload($user),
        ]);
    }

    private function userPayload($user): array
    {
        return [
            'public_id' => $user->public_id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'account_status' => $user->account_status,
            'email_verified' => $user->email_verified_at !== null,
            'phone_verified' => $user->phone_verified_at !== null,
            'fully_verified' => $user->isFullyVerified(),
        ];
    }
}
