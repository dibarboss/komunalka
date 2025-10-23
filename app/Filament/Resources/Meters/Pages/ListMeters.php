<?php

namespace App\Filament\Resources\Meters\Pages;

use App\Filament\Resources\Meters\MeterResource;
use App\Models\Address;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListMeters extends ListRecords
{
    protected static string $resource = MeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(function (): bool {
                    $tenant = Filament::getTenant();

                    if ($tenant instanceof Address) {
                        return $tenant->owner_id === auth()->id();
                    }

                    return auth()->user()?->ownedAddresses()->exists() ?? false;
                }),
        ];
    }
}
