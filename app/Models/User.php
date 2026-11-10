<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'public_id',
        'name',
        'email',
        'phone',
        'password',
        'date_of_birth',
        'country',
        'city',
        'role',
        'account_status',
        'email_verified_at',
        'phone_verified_at',
        'terms_accepted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->public_id)) {
                $user->public_id = (string) Str::uuid();
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator'], true);
    }

    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function isFullyVerified(): bool
    {
        return $this->email_verified_at !== null && $this->phone_verified_at !== null;
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function phoneVerifications()
    {
        return $this->hasMany(PhoneVerification::class);
    }
}
