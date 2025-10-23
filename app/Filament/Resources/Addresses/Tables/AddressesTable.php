<?php

namespace App\Filament\Resources\Addresses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address_line')
                    ->label('Адреса')
                    ->toggleable()
                    ->limit(40),
                TextColumn::make('city')
                    ->label('Місто')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->label('Власник')
                    ->sortable(),
                TextColumn::make('members_count')
                    ->label('Користувачі')
                    ->counts('members')
                    ->alignRight(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
