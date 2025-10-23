<?php

namespace App\Models;

use App\Models\User;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

class Address extends Model implements HasName, HasCurrentTenantLabel
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'address_line',
        'city',
        'state',
        'postal_code',
        'notes',
    ];

    protected static function booted(): void
    {
        static::created(function (self $address): void {
            $address->members()->syncWithoutDetaching([
                $address->owner_id => ['role' => 'owner'],
            ]);
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $builder) use ($user): void {
            $builder
                ->where('owner_id', $user->getKey())
                ->orWhereHas('members', fn (Builder $relation) => $relation->where('users.id', $user->getKey()));
        });
    }
//
//    public function userHasAccess(User $user): bool
//    {
//        if ($this->owner_id === $user->getKey()) {
//            return true;
//        }
//
//        return $this->members()->where('users.id', $user->getKey())->exists();
//    }
//
    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getTenantOwnershipRelationship(): Relation
    {
        return $this->owner();
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Поточна адреса';
    }
}
