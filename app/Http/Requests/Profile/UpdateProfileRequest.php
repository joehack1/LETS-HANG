<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'area' => ['sometimes', 'nullable', 'string', 'max:120'],
            'is_discoverable' => ['sometimes', 'boolean'],
            'interests' => ['sometimes', 'array'],
            'interests.*' => ['integer', 'exists:interests,id'],
        ];
    }
}
