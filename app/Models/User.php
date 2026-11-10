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

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function profilePhotos()
    {
        return $this->hasMany(ProfilePhoto::class);
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'profile_interests', 'user_id', 'interest_id');
    }

    public function sentConnectionRequests()
    {
        return $this->hasMany(ConnectionRequest::class, 'sender_id');
    }

    public function receivedConnectionRequests()
    {
        return $this->hasMany(ConnectionRequest::class, 'receiver_id');
    }

    public function blocksGiven()
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blocksReceived()
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }

    public function hasBlocked(int $userId): bool
    {
        return $this->blocksGiven()->where('blocked_id', $userId)->exists();
    }

    public function isBlockedBy(int $userId): bool
    {
        return $this->blocksReceived()->where('blocker_id', $userId)->exists();
    }

    public function isBlockedWith(int $userId): bool
    {
        return $this->hasBlocked($userId) || $this->isBlockedBy($userId);
    }

    public function phoneVerifications()
    {
        return $this->hasMany(PhoneVerification::class);
    }

    public function ensureProfile(): Profile
    {
        return $this->profile()->firstOrCreate(['user_id' => $this->id], [
            'display_name' => $this->name,
        ]);
    }

    public function ensurePreferences(): UserPreference
    {
        return $this->preferences()->firstOrCreate(['user_id' => $this->id], [
            'friendship_enabled' => true,
            'dating_enabled' => false,
            'discoverable' => true,
        ]);
    }
}
