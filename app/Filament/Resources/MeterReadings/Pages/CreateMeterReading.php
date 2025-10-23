<?php

namespace App\Filament\Resources\MeterReadings\Pages;

use App\Filament\Resources\MeterReadings\MeterReadingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateMeterReading extends CreateRecord
{
    protected static string $resource = MeterReadingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // Валідація що дата унікальна для лічильника
        Validator::make($data, [
            'reading_date' => [
                'required',
                'date',
                Rule::unique('meter_readings', 'reading_date')
                    ->where(fn ($query) => $query->where('meter_id', $data['meter_id'])),
            ],
            'value' => ['required', 'numeric', 'gte:0'],
        ])->validate();

        // Валідація що значення не менше попереднього
        $meter = \App\Models\Meter::find($data['meter_id']);
        if ($meter) {
            $previous = $meter->readings()
                ->where('reading_date', '<', $data['reading_date'])
                ->orderByDesc('reading_date')
                ->orderByDesc('id')
                ->first();

            if ($previous && $data['value'] < $previous->value) {
                throw ValidationException::withMessages([
                    'value' => 'Значення повинно бути не меншим за попереднє показання (' . $previous->value . ').',
                ]);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
