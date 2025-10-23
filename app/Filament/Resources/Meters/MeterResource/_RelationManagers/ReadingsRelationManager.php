<?php

namespace App\Filament\Resources\Meters\MeterResource\_RelationManagers;

use App\Models\MeterReading;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'readings';

    protected static ?string $recordTitleAttribute = 'reading_date';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('reading_date')
                    ->label('Дата')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection()
                    ->default(now()),
                TextInput::make('value')
                    ->label('Значення')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.001),
                FileUpload::make('photo_path')
                    ->label('Фото')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('meter-readings')
                    ->visibility('public')
                    ->downloadable()
                    ->previewable(),
                Textarea::make('notes')
                    ->label('Коментар')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reading_date')
            ->modifyQueryUsing(fn ($query) => $query->orderByDesc('reading_date')->orderByDesc('id'))
            ->defaultSort('reading_date', 'desc')
            ->columns([
                TextColumn::make('reading_date')
                    ->label('Дата')
                    ->date(),
                TextColumn::make('value')
                    ->label('Значення')
                    ->numeric(decimalPlaces: 3),
                TextColumn::make('difference')
                    ->label('Різниця')
                    ->state(fn (MeterReading $record) => $record->difference())
                    ->badge()
                    ->color(fn (?float $state) => $state !== null ? 'success' : 'gray')
                    ->placeholder('—')
                    ->numeric(decimalPlaces: 3),
                ImageColumn::make('photo_path')
                    ->label('Фото')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label('Коментар')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Створено')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Додати показання')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $this->validateReading($data);
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn (MeterReading $record) => $record->isLatestForMeter())
                    ->tooltip('Редагувати можна лише останній запис.')
                    ->mutateFormDataUsing(function (array $data, MeterReading $record): array {
                        $data['user_id'] = auth()->id();

                        return $this->validateReading($data, $record);
                    }),
                DeleteAction::make()
                    ->visible(fn (MeterReading $record) => $record->isLatestForMeter())
                    ->tooltip('Видалити можна лише останній запис.'),
            ])
            ->emptyStateHeading('Показання відсутні')
            ->emptyStateDescription('Додайте перше показання, щоб розпочати історію лічильника.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validateReading(array $data, ?MeterReading $record = null): array
    {
        $meter = $this->getOwnerRecord();

        Validator::make($data, [
            'reading_date' => [
                'required',
                'date',
                Rule::unique('meter_readings', 'reading_date')
                    ->where(fn ($query) => $query->where('meter_id', $meter->id))
                    ->ignore($record?->id),
            ],
            'value' => ['required', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
            'photo_path' => ['nullable', 'string'],
        ])->validate();

        $previous = $meter->readings()
            ->where('reading_date', '<', $data['reading_date'])
            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
            ->orderByDesc('reading_date')
            ->orderByDesc('id')
            ->first();

        if ($previous && $data['value'] < $previous->value) {
            throw ValidationException::withMessages([
                'value' => 'Значення повинно бути не меншим за попереднє.',
            ]);
        }

        return $data;
    }
}
