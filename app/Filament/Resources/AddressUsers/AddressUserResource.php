<?php

namespace App\Filament\Resources\AddressUsers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class AddressUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-users';

//    protected static string|null|\UnitEnum $navigationGroup = 'Комуналка';

    protected static ?string $label = 'Користувач';

    protected static ?string $pluralLabel = 'Користувачі';

    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        $tenant = Filament::getTenant();

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Імʼя'),
                TextColumn::make('email')
                    ->label('Email'),
                TextColumn::make('pivot.role')
                    ->label('Роль')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'owner' ? 'Власник' : 'Учасник')
                    ->color(fn (string $state): string => $state === 'owner' ? 'success' : 'gray'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Додати користувача')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) use ($tenant) {
                        if ($tenant) {
                            return $query
                                ->whereNotIn('users.id', $tenant->members()->pluck('users.id'))
                                ->orderBy('name');
                        }
                        return $query->orderBy('name');
                    })
                    ->form([
                        Select::make('role')
                            ->label('Роль')
                            ->options([
                                'owner' => 'Власник',
                                'member' => 'Учасник',
                            ])
                            ->default('member')
                            ->required(),
                    ])
                    ->visible(fn () => $tenant && $tenant->owner_id === auth()->id())
                    ->slideOver(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Змінити роль')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Select::make('role')
                            ->label('Роль')
                            ->options([
                                'owner' => 'Власник',
                                'member' => 'Учасник',
                            ])
                            ->required(),
                    ])
                    ->fillForm(fn (User $record): array => [
                        'role' => $record->pivot->role,
                    ])
                    ->action(function (User $record, array $data) use ($tenant): void {
                        if ($tenant) {
                            $tenant->members()->updateExistingPivot($record->id, ['role' => $data['role']]);
                        }
                    })
//                    ->hidden(fn (User $record): bool => $record->pivot->role === 'owner')
                    ->visible(fn () => $tenant && $tenant->owner_id === auth()->id()),
                DetachAction::make()
                    ->label('Видалити')
//                    ->hidden(fn (User $record): bool => $record->pivot->role === 'owner')
                    ->visible(fn () => $tenant && $tenant->owner_id === auth()->id()),
            ])
            ->emptyStateHeading('Немає користувачів')
            ->emptyStateDescription('Додайте користувачів, щоб ділитись введенням показань.')
            ->emptyStateActions([
                AttachAction::make()
                    ->label('Додати першого користувача')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) use ($tenant) {
                        if ($tenant) {
                            return $query
                                ->whereNotIn('users.id', $tenant->members()->pluck('users.id'))
                                ->orderBy('name');
                        }
                        return $query->orderBy('name');
                    })
                    ->form([
                        Select::make('role')
                            ->label('Роль')
                            ->options([
                                'owner' => 'Власник',
                                'member' => 'Учасник',
                            ])
                            ->default('member')
                            ->required(),

                    ])
                    ->visible(fn () => $tenant && $tenant->owner_id === auth()->id())
                    ->slideOver(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            return $tenant->members()->getQuery();
        }

        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AddressUsers\Pages\ListAddressUsers::route('/'),
        ];
    }
}
