<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_name',
        'guest_phone',
        'worker_id',
        'service_id',
        'status',
        'amount',
        'latitude',
        'longitude',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'amount' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }

    public function formattedAmount(): string
    {
        return '₦'.number_format($this->amount);
    }
}
