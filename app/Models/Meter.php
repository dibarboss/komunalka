<?php

namespace App\Models;

use App\Enums\MeterType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_id',
        'type',
        'unit',
        'description',
        'submission_day',
    ];

    protected $casts = [
        'type' => MeterType::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $meter): void {
            if (! $meter->unit && $meter->type instanceof MeterType) {
                $meter->unit = $meter->type->defaultUnit();
            }
        });
    }

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function owner(): ?User
    {
        return $this->address?->owner;
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->whereIn('address_id', $user->accessibleAddressesQuery()->select('addresses.id'));
    }

    public function typeLabel(): string
    {
        return $this->type instanceof MeterType ? $this->type->getLabel() : (string) $this->type;
    }
}
