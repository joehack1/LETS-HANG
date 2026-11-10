<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class MpesaService
{
    public function baseUrl(): string
    {
        return config('services.mpesa.env', 'sandbox') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function accessToken(): string
    {
        $key = config('services.mpesa.consumer_key');
        $secret = config('services.mpesa.consumer_secret');

        $response = Http::withBasicAuth($key, $secret)
            ->get($this->baseUrl().'/oauth/v1/generate?grant_type=client_credentials');

        return $response->json('access_token');
    }

    public function stkPush(string $phone, float $amount, string $reference, string $description): array
    {
        $shortcode = config('services.mpesa.shortcode');
        $passkey = config('services.mpesa.passkey');
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) round($amount),
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => config('services.mpesa.callback_url'),
            'AccountReference' => $reference,
            'TransactionDesc' => $description,
        ];

        $response = Http::withToken($this->accessToken())
            ->post($this->baseUrl().'/mpesa/stkpush/v1/processrequest', $payload);

        return $response->json() ?? [];
    }
}
