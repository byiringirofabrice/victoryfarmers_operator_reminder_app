<?php

namespace App\Filament\Resources\ControlRoomResource\Pages;

use App\Filament\Resources\ControlRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControlRoom extends EditRecord
{
    protected static string $resource = ControlRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
