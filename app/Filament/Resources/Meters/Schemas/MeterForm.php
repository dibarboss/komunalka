<?php

namespace App\Filament\Resources\Meters\Schemas;

use App\Enums\MeterType;
use App\Models\Address;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MeterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('address_id')
                    ->label('Адреса')
                    ->options(fn (): array => auth()->user()?->accessibleAddressesQuery()->pluck('name', 'id')->toArray() ?? [])
                    ->searchable()
                    ->preload()
                    ->required(fn () => Filament::getTenant() === null)
                    ->native(false)
                    ->hidden(fn () => Filament::getTenant() !== null)
                    ->default(fn () => Filament::getTenant()?->id)
                    ->disabled(fn () => Filament::getTenant() !== null)
                    ->dehydrated(),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Тип')
                            ->options(MeterType::class)
                            ->enum(MeterType::class)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state, Get $get): void {
                                if (! $get('unit') && $state) {
                                    $type = MeterType::tryFrom($state->value);

                                    if ($type) {
                                        $set('unit', $type->defaultUnit());
                                    }
                                }
                            }),
                        TextInput::make('submission_day')
                            ->label('День подання показань')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->helperText('День місяця, коли потрібно подавати показання (1-31)'),
                    ]),
                TextInput::make('unit')
                    ->label('Одиниця виміру')
                    ->maxLength(50)
                    ->helperText('Наприклад: м³, кВт·год'),
                Textarea::make('description')
                    ->label('Опис')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
