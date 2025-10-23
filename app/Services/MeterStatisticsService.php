<?php

namespace App\Services;

use App\Enums\MeterType;
use App\Models\Meter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MeterStatisticsService
{
    /**
     * @return array<string, mixed>
     */
    public function calculate(Meter $meter): array
    {
        $meter->loadMissing('readings');

        $readings = $meter->readings()->orderBy('reading_date')->orderBy('id')->get();

        $history = [];
        $previous = null;
        $totalConsumption = 0.0;

        foreach ($readings as $reading) {
            $difference = $previous ? round($reading->value - $previous->value, 3) : null;

            if ($difference !== null) {
                $totalConsumption += $difference;
            }

            $history[] = [
                'id' => $reading->id,
                'reading_date' => $reading->reading_date->toDateString(),
                'value' => $reading->value,
                'difference' => $difference,
                'photo_url' => $reading->photo_path ? Storage::disk('public')->url($reading->photo_path) : null,
                'notes' => $reading->notes,
            ];

            $previous = $reading;
        }

        $periodDays = 0;
        $averageDailyConsumption = null;

        if ($readings->count() >= 2) {
            $periodDays = $readings->first()->reading_date->diffInDays($readings->last()->reading_date);
            if ($periodDays > 0 && $totalConsumption > 0) {
                $averageDailyConsumption = round($totalConsumption / $periodDays, 3);
            }
        }

        $monthlySeries = collect($history)
            ->filter(fn (array $entry) => $entry['difference'] !== null)
            ->groupBy(fn (array $entry) => substr($entry['reading_date'], 0, 7))
            ->map(fn (Collection $group) => round($group->sum('difference'), 3))
            ->map(fn (float $value, string $period) => [
                'period' => $period,
                'consumption' => $value,
            ])
            ->values();

        // Розраховуємо тренди
        $lastMonthConsumption = null;
        $previousMonthConsumption = null;
        $consumptionTrend = null;
        $consumptionTrendPercentage = null;

        if ($monthlySeries->count() >= 2) {
            $lastMonthConsumption = $monthlySeries->last()['consumption'] ?? null;
            $previousMonthConsumption = $monthlySeries->slice(-2, 1)->first()['consumption'] ?? null;

            if ($lastMonthConsumption !== null && $previousMonthConsumption !== null && $previousMonthConsumption > 0) {
                $consumptionTrend = round($lastMonthConsumption - $previousMonthConsumption, 3);
                $consumptionTrendPercentage = round(($consumptionTrend / $previousMonthConsumption) * 100, 1);
            }
        }

        return [
            'meter' => [
                'id' => $meter->id,
                'type' => $meter->type?->value,
                'type_label' => $meter->type instanceof MeterType ? $meter->type->getLabel() : null,
                'unit' => $meter->unit,
                'submission_day' => $meter->submission_day,
            ],
            'summary' => [
                'total_readings' => $readings->count(),
                'total_consumption' => round($totalConsumption, 3),
                'average_daily_consumption' => $averageDailyConsumption,
                'period_days' => $periodDays,
            ],
            'trends' => [
                'last_month_consumption' => $lastMonthConsumption,
                'previous_month_consumption' => $previousMonthConsumption,
                'consumption_trend' => $consumptionTrend,
                'consumption_trend_percentage' => $consumptionTrendPercentage,
            ],
            'history' => array_reverse($history),
            'chart' => $monthlySeries,
        ];
    }
}
