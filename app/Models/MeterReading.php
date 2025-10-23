<?php

namespace App\Models;

use App\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_id',
        'user_id',
        'reading_date',
        'value',
        'photo_path',
        'notes',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'value' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $reading): void {
            $owner = $reading->meter?->owner();

            if (! $reading->user_id && $owner) {
                $reading->user_id = $owner->getKey();
            }
        });
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): ?Address
    {
        return $this->meter?->address;
    }

    public function owner(): ?BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMeter(Builder $query, Meter $meter): Builder
    {
        return $query->where('meter_id', $meter->getKey());
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('reading_date')->orderByDesc('id');
    }

    public function difference(): ?float
    {
        $previous = $this->previousReading();

        if (! $previous) {
            return null;
        }

        return round($this->value - $previous->value, 3);
    }

    public function previousReading(): ?self
    {
        return $this->meter?->readings()
            ->where('reading_date', '<', $this->reading_date)
            ->orderByDesc('reading_date')
            ->orderByDesc('id')
            ->first();
    }

    public function isLatestForMeter(): bool
    {
        $latest = $this->meter?->readings()
            ->orderByDesc('reading_date')
            ->orderByDesc('id')
            ->first();

        return $latest?->is($this) ?? false;
    }
}
