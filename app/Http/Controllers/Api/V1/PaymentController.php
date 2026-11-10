<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payments\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function checkout(Request $request, MpesaService $mpesa): JsonResponse
    {
        $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'provider' => ['sometimes', 'in:mpesa,card'],
            'phone' => ['required_if:provider,mpesa', 'string', 'max:32'],
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail($request->integer('plan_id'));
        $provider = $request->string('provider', 'mpesa')->toString();

        $payment = Payment::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'provider' => $provider,
            'status' => 'pending',
            'reference' => (string) Str::uuid(),
        ]);

        if ($provider === 'mpesa') {
            $result = $mpesa->stkPush(
                $request->string('phone')->toString(),
                (float) $plan->price,
                $payment->reference,
                'Let\'s Hang '.$plan->name
            );

            $payment->update([
                'provider_reference' => $result['CheckoutRequestID'] ?? null,
                'metadata' => $result,
            ]);

            if (($result['ResponseCode'] ?? '1') !== '0') {
                $payment->update(['status' => 'failed']);

                return response()->json([
                    'message' => $result['ResponseDescription'] ?? 'Payment initiation failed.',
                    'payment' => $payment->fresh(),
                ], 422);
            }
        }

        return response()->json([
            'message' => 'Payment initiated. Awaiting confirmation.',
            'payment' => $payment->fresh(),
        ], 201);
    }

    public function mpesaCallback(Request $request): JsonResponse
    {
        $payload = $request->all();
        $stk = $payload['Body']['stkCallback'] ?? [];
        $checkoutId = $stk['CheckoutRequestID'] ?? null;
        $resultCode = $stk['ResultCode'] ?? null;

        $payment = Payment::where('provider_reference', $checkoutId)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if ((int) $resultCode === 0) {
            $payment->update(['status' => 'successful', 'metadata' => $payload]);

            $subscription = Subscription::create([
                'user_id' => $payment->user_id,
                'plan_id' => $payment->plan_id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $payment->plan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            $payment->update(['subscription_id' => $subscription->id]);
        } else {
            $payment->update(['status' => 'failed', 'metadata' => $payload]);
        }

        return response()->json(['message' => 'Callback processed.']);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $payment = Payment::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['payment' => $payment->load('plan', 'subscription')]);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'is_premium' => $user->isPremium(),
            'subscription' => $user->activeSubscription()?->load('plan'),
        ]);
    }
}
