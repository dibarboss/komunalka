<?php

namespace App\Filament\Resources\Meters;

//use App\Filament\Resources\Meters\MeterResource\_RelationManagers\ReadingsRelationManager;
use App\Filament\Resources\Meters\Pages\CreateMeter;
use App\Filament\Resources\Meters\Pages\EditMeter;
use App\Filament\Resources\Meters\Pages\ListMeters;
use App\Filament\Resources\Meters\Pages\ViewMeter;
use App\Filament\Resources\Meters\Schemas\MeterForm;
use App\Filament\Resources\Meters\Tables\MetersTable;
use App\Models\Address;
use App\Models\Meter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class MeterResource extends Resource
{
    protected static ?string $model = Meter::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

//    protected static string|null|\UnitEnum $navigationGroup = 'Комуналка';

    protected static ?string $label = 'Лічильник';

    protected static ?string $pluralLabel = 'Лічильники';

    protected static bool $isScopedToTenant = true;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MeterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
//            ReadingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeters::route('/'),
            'create' => CreateMeter::route('/create'),
            'view' => ViewMeter::route('/{record}'),
            'edit' => EditMeter::route('/{record}/edit'),
        ];
    }

    public static function getTenantOwnershipRelationship(Model $record): Relation
    {
        return Filament::getTenant()->owner();
    }
}
