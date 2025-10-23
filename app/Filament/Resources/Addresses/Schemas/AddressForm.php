<?php

namespace App\Filament\Resources\Addresses\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class AddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line')
                    ->label('Адреса')
                    ->maxLength(255),
                Grid::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('city')
                            ->label('Місто')
                            ->maxLength(120),
                        TextInput::make('state')
                            ->label('Область')
                            ->maxLength(120),
                        TextInput::make('postal_code')
                            ->label('Поштовий індекс')
                            ->maxLength(30),
                    ]),
                Textarea::make('notes')
                    ->label('Примітки')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
