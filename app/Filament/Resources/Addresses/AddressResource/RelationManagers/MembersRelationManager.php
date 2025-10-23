<?php

namespace App\Filament\Resources\Addresses\AddressResource\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('role')
                    ->label('Роль')
                    ->options([
                        'owner' => 'Власник',
                        'member' => 'Учасник',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Імʼя')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('pivot.role')
                    ->label('Роль')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'owner' ? 'Власник' : 'Учасник'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Додати користувача')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('name'))
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
                    ->visible(fn (): bool => $this->getOwnerRecord()->owner_id === auth()->id())
                    ->slideOver(),
            ])
            ->actions([
                EditAction::make()
                    ->hidden(fn ($record): bool => $record->pivot->role === 'owner')
                    ->visible(fn (): bool => $this->getOwnerRecord()->owner_id === auth()->id()),
                DetachAction::make()
                    ->hidden(fn ($record): bool => $record->pivot->role === 'owner')
                    ->visible(fn (): bool => $this->getOwnerRecord()->owner_id === auth()->id()),
            ])
            ->emptyStateHeading('Немає користувачів')
            ->emptyStateDescription('Додайте користувачів, щоб ділитись введенням показань.');
    }
}
