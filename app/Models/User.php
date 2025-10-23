<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Address;
use App\Models\MeterReading;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function ownedAddresses(): HasMany
    {
        return $this->hasMany(Address::class, 'owner_id');
    }

    public function addreses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class);
    }

    public function accessibleAddressIds(): array
    {
        $owned = $this->ownedAddresses()->pluck('id');
        $member = $this->addresses()->pluck('addresses.id');

        return $owned->merge($member)->unique()->values()->all();
    }

    public function accessibleAddressesQuery(): Builder
    {
        return Address::query()->where(function (Builder $query): void {
            $query->where('owner_id', $this->getKey())
                ->orWhereHas('members', fn (Builder $members) => $members->where('users.id', $this->getKey()));
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->addreses;
    }

//    public function canAccessTenant($tenant): bool
//    {
//        return $tenant instanceof Address && $tenant->userHasAccess($this);
//    }
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->addreses->contains($tenant);
    }
}
