<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MeterReadingsReminder;
use App\Filament\Widgets\MeterStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Головна';

    public function getWidgets(): array
    {
        return [
            MeterStatsWidget::class,
            MeterReadingsReminder::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 1;
    }
}
