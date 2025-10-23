<?php

namespace App\Filament\Resources\AddressUsers\Pages;

use App\Filament\Resources\AddressUsers\AddressUserResource;
use Filament\Resources\Pages\ListRecords;

class ListAddressUsers extends ListRecords
{
    protected static string $resource = AddressUserResource::class;

    protected static ?string $title = 'Користувачі';
}