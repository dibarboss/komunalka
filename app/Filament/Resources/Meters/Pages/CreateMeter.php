<?php

namespace App\Filament\Resources\Meters\Pages;

use App\Filament\Resources\Meters\MeterResource;
use App\Models\Address;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateMeter extends CreateRecord
{
    protected static string $resource = MeterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Address) {
            abort_unless($tenant->owner_id === auth()->id(), 403);
            $data['address_id'] = $tenant->getKey();
        } else {
            $address = Address::findOrFail($data['address_id'] ?? null);
            abort_unless($address->owner_id === auth()->id(), 403);
        }

        return $data;
    }
}
