<?php

namespace App\Filament\Resources\MeterReadings;

use App\Models\MeterReading;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class MeterReadingResource extends Resource
{
    protected static ?string $model = MeterReading::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chart-bar';

//    protected static string|null|\UnitEnum $navigationGroup = 'Комуналка';

    protected static ?string $label = 'Показання';

    protected static ?string $pluralLabel = 'Показання';

    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Фільтруємо показання тільки для доступних адрес користувача
        if (Filament::getTenant()) {
            // Якщо є tenant (адреса), показуємо тільки показання цієї адреси
            $query->whereHas('meter', fn ($q) => $q->where('address_id', Filament::getTenant()->id));
        } else {
            // Якщо немає tenant, показуємо показання для всіх доступних адрес користувача
            $query->whereHas('meter', fn ($q) => $q->whereIn('address_id', auth()->user()?->accessibleAddressesQuery()->select('addresses.id') ?? []));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('meter_id')
                    ->label('Лічильник')
                    ->relationship(
                        name: 'meter',
                        titleAttribute: 'type',
                        modifyQueryUsing: fn ($query) => $query
                            ->with('address')
                            ->when(Filament::getTenant(), fn ($q, $tenant) => $q->where('address_id', $tenant->id))
                            ->when(!Filament::getTenant(), fn ($q) => $q->whereIn('address_id', auth()->user()?->accessibleAddressesQuery()->select('addresses.id') ?? [])),
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->typeLabel() . ' (' . $record->address?->name . ')')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('reading_date', 'desc')
            ->columns([
                TextColumn::make('meter.type')
                    ->label('Лічильник')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reading_date')
                    ->label('Дата')
                    ->date()
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Значення')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
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
                TextColumn::make('user.name')
                    ->label('Автор')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Створено')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('meter_id')
                    ->label('Лічильник')
                    ->relationship(
                        name: 'meter',
                        titleAttribute: 'type',
                        modifyQueryUsing: fn ($query) => $query->with('address'),
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->typeLabel())
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (MeterReading $record) => $record->isLatestForMeter())
                    ->tooltip('Редагувати можна лише останній запис.'),
                DeleteAction::make()
                    ->visible(fn (MeterReading $record) => $record->isLatestForMeter())
                    ->tooltip('Видалити можна лише останній запис.'),
            ])
            ->emptyStateHeading('Показання відсутні')
            ->emptyStateDescription('Додайте перше показання.');
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return $record->isLatestForMeter();
    }

    public static function canDelete(Model $record): bool
    {
        return $record->isLatestForMeter();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\MeterReadings\Pages\ListMeterReadings::route('/'),
            'create' => \App\Filament\Resources\MeterReadings\Pages\CreateMeterReading::route('/create'),
            'edit' => \App\Filament\Resources\MeterReadings\Pages\EditMeterReading::route('/{record}/edit'),
        ];
    }

    public static function getTenantOwnershipRelationship(Model $record): Relation
    {
        return Filament::getTenant()->owner();
    }
}
