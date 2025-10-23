<?php

namespace App\Filament\Resources\Meters\Tables;

use App\Models\Meter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MetersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->sortable(),
                TextColumn::make('submission_day')
                    ->label('День подання')
                    ->sortable()
                    ->placeholder('—')
                    ->suffix(' число'),
                TextColumn::make('unit')
                    ->label('Одиниця')
                    ->searchable(),
                TextColumn::make('latest_reading.value')
                    ->label('Останнє значення')
                    ->state(fn (Meter $record) => optional($record->readings()->orderByDesc('reading_date')->orderByDesc('id')->first())->value)
                    ->numeric(
                        decimalPlaces: 3,
                    )
                    ->alignRight()
                    ->placeholder('—'),
                TextColumn::make('readings_count')
                    ->label('Записів')
                    ->counts('readings')
                    ->alignRight()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Meter $record) => route('filament.dashboard.resources.meters.view', ['tenant' => $record->address_id, 'record' => $record]));
    }
}
