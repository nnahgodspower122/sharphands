<?php

namespace App\Models;

use App\Enums\WorkerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    /** @use HasFactory<\Database\Factories\WorkerFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'name',
        'phone',
        'latitude',
        'longitude',
        'status',
        'verified',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'status' => WorkerStatus::class,
            'verified' => 'boolean',
            'applied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating(): float
    {
        return round((float) $this->ratings()->avg('score'), 1);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(substr($parts[0] ?? 'W', 0, 1).substr($parts[1] ?? '', 0, 1));
    }
}
