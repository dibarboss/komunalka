<?php

namespace App\Filament\Widgets;

use App\Models\Meter;
use App\Models\MeterReading;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MeterStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        // Запит для лічильників
        $metersQuery = Meter::query()
            ->when($tenant, fn ($q) => $q->where('address_id', $tenant->id))
            ->when(!$tenant, fn ($q) => $q->whereIn('address_id', auth()->user()?->accessibleAddressesQuery()->select('addresses.id') ?? []));

        $totalMeters = $metersQuery->count();

        // Лічильники з встановленим днем подання
        $metersWithSubmissionDay = (clone $metersQuery)->whereNotNull('submission_day')->count();

        // Показання за поточний місяць
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $readingsThisMonth = MeterReading::query()
            ->whereHas('meter', function ($q) use ($tenant) {
                $q->when($tenant, fn ($query) => $query->where('address_id', $tenant->id))
                  ->when(!$tenant, fn ($query) => $query->whereIn('address_id', auth()->user()?->accessibleAddressesQuery()->select('addresses.id') ?? []));
            })
            ->whereYear('reading_date', $currentYear)
            ->whereMonth('reading_date', $currentMonth)
            ->count();

        // Показання за минулий місяць для порівняння
        $lastMonth = Carbon::now()->subMonth();
        $readingsLastMonth = MeterReading::query()
            ->whereHas('meter', function ($q) use ($tenant) {
                $q->when($tenant, fn ($query) => $query->where('address_id', $tenant->id))
                  ->when(!$tenant, fn ($query) => $query->whereIn('address_id', auth()->user()?->accessibleAddressesQuery()->select('addresses.id') ?? []));
            })
            ->whereYear('reading_date', $lastMonth->year)
            ->whereMonth('reading_date', $lastMonth->month)
            ->count();

        $readingsDiff = $readingsThisMonth - $readingsLastMonth;

        return [
            Stat::make('Всього лічильників', $totalMeters)
                ->description('Активних у системі')
                ->icon('heroicon-o-chart-bar-square')
                ->color('primary'),
            Stat::make('З налаштованим днем подання', $metersWithSubmissionDay)
                ->description("З {$totalMeters} лічильників")
                ->icon('heroicon-o-calendar-days')
                ->color('info'),
            Stat::make('Показань цього місяця', $readingsThisMonth)
                ->description($readingsDiff > 0 ? "+{$readingsDiff} більше ніж минулого" : ($readingsDiff < 0 ? "{$readingsDiff} менше ніж минулого" : 'Без змін'))
                ->descriptionIcon($readingsDiff > 0 ? 'heroicon-m-arrow-trending-up' : ($readingsDiff < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($readingsDiff >= 0 ? 'success' : 'warning')
                ->icon('heroicon-o-clipboard-document-check'),
        ];
    }
}
