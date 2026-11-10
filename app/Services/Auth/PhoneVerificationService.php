<?php

namespace App\Services\Auth;

use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PhoneVerificationService
{
    public function create(User $user, ?string $phone = null): PhoneVerification
    {
        $phone = $phone ?? $user->phone;

        $otp = (string) random_int(100000, 999999);

        $record = PhoneVerification::create([
            'user_id' => $user->id,
            'phone' => $phone,
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        // In production, dispatch an SMS job here. For now, log for dev.
        logger()->info('Phone OTP generated', ['user_id' => $user->id, 'otp' => $otp]);

        return $record;
    }

    public function verify(User $user, string $otp): bool
    {
        $record = PhoneVerification::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $record) {
            return false;
        }

        if ($record->expires_at->isPast()) {
            return false;
        }

        if ($record->attempts >= 5) {
            return false;
        }

        $record->increment('attempts');

        if (! Hash::check($otp, $record->otp_hash)) {
            return false;
        }

        $record->update(['verified_at' => now()]);
        $user->update([
            'phone' => $record->phone,
            'phone_verified_at' => now(),
        ]);

        return true;
    }
}
