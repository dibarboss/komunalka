<?php

namespace App\Filament\Widgets;

use App\Models\Meter;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MeterReadingsReminder extends TableWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Нагадування про показання лічильників')
            ->description('Лічильники, для яких наближається або минув термін подання показань')
            ->query(
                Meter::query()
                    ->with(['readings' => fn ($q) => $q->orderByDesc('reading_date')->orderByDesc('id')->limit(1)])
                    ->when(Filament::getTenant(), fn ($q, $tenant) => $q->where('address_id', $tenant->id))
                    ->when(!Filament::getTenant(), fn ($q) => $q->whereIn('address_id', auth()->user()?->accessibleAddressesQuery()->select('addresses.id') ?? []))
                    ->whereNotNull('submission_day')
                    ->orderBy('submission_day')
            )
            ->columns([
                TextColumn::make('type')
                    ->label('Тип лічильника')
                    ->badge(),
                TextColumn::make('submission_day')
                    ->label('День подання')
                    ->suffix(' число')
                    ->sortable(),
                TextColumn::make('latest_reading')
                    ->label('Останнє показання')
                    ->state(function (Meter $record) {
                        $latest = $record->readings->first();
                        if (!$latest) {
                            return '—';
                        }
                        return $latest->reading_date->format('d.m.Y') . ' (' . number_format($latest->value, 3) . ' ' . $record->unit . ')';
                    }),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->state(function (Meter $record) {
                        if (!$record->submission_day) {
                            return 'Не встановлено';
                        }

                        $latest = $record->readings->first();
                        $today = Carbon::today();
                        $currentMonth = $today->month;
                        $currentYear = $today->year;
                        $submissionDay = $record->submission_day;

                        // Дата подання показань цього місяця
                        $submissionDate = Carbon::create($currentYear, $currentMonth, min($submissionDay, $today->daysInMonth));

                        if (!$latest) {
                            return 'Немає показань';
                        }

                        // Перевіряємо чи показання внесені за поточний місяць
                        $latestDate = $latest->reading_date;

                        if ($latestDate->year === $currentYear && $latestDate->month === $currentMonth) {
                            return 'Внесено';
                        }

                        // Якщо показання не внесені і термін минув
                        if ($today->greaterThan($submissionDate)) {
                            $daysLate = $today->diffInDays($submissionDate);
                            return "Прострочено ({$daysLate} дн.)";
                        }

                        // Якщо до терміну залишилося менше 3 днів
                        $daysUntil = $today->diffInDays($submissionDate, false);
                        if ($daysUntil >= 0 && $daysUntil <= 3) {
                            return "Скоро ({$daysUntil} дн.)";
                        }

                        return 'Внесено';
                    })
                    ->color(function (Meter $record): string {
                        if (!$record->submission_day) {
                            return 'gray';
                        }

                        $latest = $record->readings->first();
                        $today = Carbon::today();
                        $currentMonth = $today->month;
                        $currentYear = $today->year;
                        $submissionDay = $record->submission_day;

                        $submissionDate = Carbon::create($currentYear, $currentMonth, min($submissionDay, $today->daysInMonth));

                        if (!$latest) {
                            return 'danger';
                        }

                        $latestDate = $latest->reading_date;

                        if ($latestDate->year === $currentYear && $latestDate->month === $currentMonth) {
                            return 'success';
                        }

                        if ($today->greaterThan($submissionDate)) {
                            return 'danger';
                        }

                        $daysUntil = $today->diffInDays($submissionDate, false);
                        if ($daysUntil >= 0 && $daysUntil <= 3) {
                            return 'warning';
                        }

                        return 'success';
                    }),
            ])
            ->recordAction(null)
            ->recordUrl(fn (Meter $record) => route('filament.dashboard.resources.meters.view', ['tenant' => $record->address_id, 'record' => $record]))
            ->paginated(false);
    }
}
