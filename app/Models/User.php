<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'avatar_url',
        'password',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function worker(): HasOne
    {
        return $this->hasOne(Worker::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isWorker(): bool
    {
        return $this->role === UserRole::Worker;
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::User;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(substr($parts[0] ?? 'U', 0, 1).substr($parts[1] ?? '', 0, 1));
    }

    public function issueApiToken(): string
    {
        $plain = Str::random(60);

        $this->forceFill(['api_token' => hash('sha256', $plain)])->save();

        return $plain;
    }

    public function toAuthArray(?string $plainToken = null): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role->value,
            'initials' => $this->initials(),
            'token' => $plainToken,
        ];
    }
}
