<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MeterType: string implements HasLabel, HasColor
{
    case ColdWater = 'cold_water';
    case HotWater = 'hot_water';
    case Gas = 'gas';
    case Electricity = 'electricity';
    case Heating = 'heating';

    public function getLabel(): string
    {
        return match ($this) {
            self::ColdWater => 'Холодна вода',
            self::HotWater => 'Гаряча вода',
            self::Gas => 'Газ',
            self::Electricity => 'Електроенергія',
            self::Heating => 'Тепло',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ColdWater => 'info',      // синій
            self::HotWater => 'danger',     // червоний
            self::Gas => 'warning',         // жовтий
            self::Electricity => 'success', // зелений
            self::Heating => 'gray',        // сірий
        };
    }

    public function defaultUnit(): string
    {
        return match ($this) {
            self::ColdWater, self::HotWater => 'м³',
            self::Gas => 'м³',
            self::Electricity => 'кВт·год',
            self::Heating => 'Гкал',
        };
    }
}
