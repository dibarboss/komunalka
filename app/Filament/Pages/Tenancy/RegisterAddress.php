<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Address;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;

class RegisterAddress extends RegisterTenant
{
    protected static ?string $title = 'Створити адресу';

    public static function getLabel(): string
    {
        return 'Нова адреса';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Наприклад: "Квартира", "Дача", "Офіс"'),
                TextInput::make('address_line')
                    ->label('Адреса')
                    ->maxLength(255)
                    ->helperText('Вулиця, будинок, квартира'),
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

    protected function handleRegistration(array $data): Address
    {
        return Address::create([
            ...$data,
            'owner_id' => auth()->id(),
        ]);
    }
}
