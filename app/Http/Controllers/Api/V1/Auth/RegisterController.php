<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Auth\PhoneVerificationService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, PhoneVerificationService $phoneService): JsonResponse
    {
        $data = $request->validated();

        $dob = Carbon::parse($data['date_of_birth']);
        if ($dob->age < 18) {
            return response()->json([
                'message' => 'You must be at least 18 years old to register.',
                'errors' => ['date_of_birth' => ['You must be at least 18 years old.']],
            ], 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'date_of_birth' => $data['date_of_birth'],
            'country' => strtoupper($data['country']),
            'city' => $data['city'],
            'terms_accepted_at' => now(),
        ]);

        event(new Registered($user));

        $phoneService->create($user);

        return response()->json([
            'message' => 'Registration successful. Please verify your email and phone.',
            'user' => [
                'public_id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified' => false,
                'phone_verified' => false,
            ],
        ], 201);
    }
}
