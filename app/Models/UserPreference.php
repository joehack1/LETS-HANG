<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'friendship_enabled',
        'dating_enabled',
        'discoverable',
        'preferred_intentions',
        'age_min',
        'age_max',
        'distance_km',
        'preferred_cities',
    ];

    protected function casts(): array
    {
        return [
            'friendship_enabled' => 'boolean',
            'dating_enabled' => 'boolean',
            'discoverable' => 'boolean',
            'preferred_intentions' => 'array',
            'preferred_cities' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
