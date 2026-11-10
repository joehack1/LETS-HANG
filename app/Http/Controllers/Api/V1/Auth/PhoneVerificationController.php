<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendPhoneOtpRequest;
use App\Http\Requests\Auth\VerifyPhoneOtpRequest;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Http\JsonResponse;

class PhoneVerificationController extends Controller
{
    public function send(SendPhoneOtpRequest $request, PhoneVerificationService $service): JsonResponse
    {
        $user = $request->user();

        if ($request->filled('phone')) {
            $user->phone = $request->string('phone')->toString();
            $user->save();
        }

        if (empty($user->phone)) {
            return response()->json(['message' => 'Phone number is required.'], 422);
        }

        $service->create($user, $user->phone);

        return response()->json(['message' => 'OTP sent.']);
    }

    public function verify(VerifyPhoneOtpRequest $request, PhoneVerificationService $service): JsonResponse
    {
        $user = $request->user();

        if ($service->verify($user, $request->string('otp')->toString())) {
            return response()->json(['message' => 'Phone verified successfully.']);
        }

        return response()->json(['message' => 'Invalid or expired OTP.'], 422);
    }
}
