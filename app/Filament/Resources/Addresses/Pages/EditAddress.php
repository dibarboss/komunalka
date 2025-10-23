<?php

namespace App\Filament\Resources\Addresses\Pages;

use App\Filament\Resources\Addresses\AddressResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAddress extends EditRecord
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->owner_id === auth()->id()),
        ];
    }

    protected function beforeFill(): void
    {
        $this->ensureOwner();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->ensureOwner();

        unset($data['owner_id']);

        return $data;
    }

    private function ensureOwner(): void
    {
        abort_unless($this->record->owner_id === auth()->id(), 403);
    }
}
