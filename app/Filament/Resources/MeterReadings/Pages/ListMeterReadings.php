<?php

namespace App\Filament\Resources\MeterReadings\Pages;

use App\Enums\MeterType;
use App\Filament\Resources\MeterReadings\MeterReadingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMeterReadings extends ListRecords
{
    protected static string $resource = MeterReadingResource::class;

    protected static ?string $title = 'Показання';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Додати показання'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Всі'),
            'cold_water' => Tab::make('Холодна вода')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('meter', fn ($q) => $q->where('type', MeterType::ColdWater))),
            'hot_water' => Tab::make('Гаряча вода')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('meter', fn ($q) => $q->where('type', MeterType::HotWater))),
            'gas' => Tab::make('Газ')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('meter', fn ($q) => $q->where('type', MeterType::Gas))),
            'electricity' => Tab::make('Електроенергія')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('meter', fn ($q) => $q->where('type', MeterType::Electricity))),
            'heating' => Tab::make('Тепло')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('meter', fn ($q) => $q->where('type', MeterType::Heating))),
        ];
    }
}
