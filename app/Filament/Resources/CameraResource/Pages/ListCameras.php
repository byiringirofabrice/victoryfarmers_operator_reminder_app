<?php

namespace App\Filament\Resources\CameraResource\Pages;

use App\Filament\Resources\CameraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCameras extends ListRecords
{
    protected static string $resource = CameraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\CameraImporter::class)
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('success'),
        ];
    }
}