<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'friendship_enabled' => ['sometimes', 'boolean'],
            'dating_enabled' => ['sometimes', 'boolean'],
            'discoverable' => ['sometimes', 'boolean'],
            'preferred_intentions' => ['sometimes', 'nullable', 'array'],
            'preferred_intentions.*' => ['string', 'in:friendship,dating'],
            'age_min' => ['sometimes', 'nullable', 'integer', 'min:18', 'max:99'],
            'age_max' => ['sometimes', 'nullable', 'integer', 'min:18', 'max:99'],
            'distance_km' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:500'],
            'preferred_cities' => ['sometimes', 'nullable', 'array'],
            'preferred_cities.*' => ['string', 'max:100'],
        ];
    }
}
