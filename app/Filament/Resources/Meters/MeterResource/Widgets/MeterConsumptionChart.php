<?php

namespace App\Filament\Resources\Meters\MeterResource\Widgets;

use App\Models\Meter;
use App\Services\MeterStatisticsService;
use Filament\Widgets\ChartWidget;

class MeterConsumptionChart extends ChartWidget
{
    public ?Meter $record = null;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        if (! $this->record) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $statistics = app(MeterStatisticsService::class)->calculate($this->record);
        $chart = collect($statistics['chart']);

        return [
            'datasets' => [
                [
                    'label' => 'Споживання, ' . $this->record->unit,
                    'data' => $chart->pluck('consumption')->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $chart->pluck('period')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Місячне споживання';
    }
}
