<?php

namespace App\Filament\Resources\Meters\MeterResource\Widgets;

use App\Models\Meter;
use App\Services\MeterStatisticsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MeterStatsOverview extends StatsOverviewWidget
{
    public ?Meter $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $statistics = app(MeterStatisticsService::class)->calculate($this->record);
        $summary = $statistics['summary'];
        $trends = $statistics['trends'];
        $unit = $this->record->unit;

        $stats = [
            Stat::make('Всього показань', (string) $summary['total_readings'])
                ->description('Записів в історії')
                ->icon('heroicon-o-clipboard-document-check'),
            Stat::make('Сумарне споживання', number_format($summary['total_consumption'], 3))
                ->description($unit)
                ->icon('heroicon-o-chart-bar'),
        ];

        // Середньодобове споживання з трендом
        $avgDailyStat = Stat::make('Середньодобове', $summary['average_daily_consumption'] !== null ? number_format($summary['average_daily_consumption'], 3) : '—')
            ->icon('heroicon-o-sparkles');

        if ($summary['average_daily_consumption'] !== null) {
            $avgDailyStat->description($unit . ' / день');
        } else {
            $avgDailyStat->description('Недостатньо даних');
        }

        $stats[] = $avgDailyStat;

        // Додаємо карту тренду місячного споживання
        if ($trends['consumption_trend'] !== null && $trends['consumption_trend_percentage'] !== null) {
            $trendValue = $trends['consumption_trend'];
            $trendPercentage = $trends['consumption_trend_percentage'];

            $trendStat = Stat::make('Зміна споживання', number_format(abs($trendValue), 3) . ' ' . $unit)
                ->description(abs($trendPercentage) . '% ' . ($trendValue >= 0 ? 'збільшення' : 'зменшення'))
                ->descriptionIcon($trendValue >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trendValue >= 0 ? 'danger' : 'success');

            $stats[] = $trendStat;
        }

        return $stats;
    }
}
