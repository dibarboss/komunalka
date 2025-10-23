<?php

namespace App\Filament\Resources\Meters\Pages;

use App\Filament\Resources\Meters\MeterResource;
use App\Filament\Resources\Meters\MeterResource\Widgets\MeterConsumptionChart;
use App\Filament\Resources\Meters\MeterResource\Widgets\MeterStatsOverview;
use App\Models\Meter;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewMeter extends ViewRecord
{
    protected static string $resource = MeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->hidden(fn (Meter $record) => $record->address?->owner_id !== auth()->id()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MeterStatsOverview::class,
            MeterConsumptionChart::class,
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([]);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }
}
