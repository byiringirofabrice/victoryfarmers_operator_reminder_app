<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\UsersWidget;
use App\Filament\Widgets\CountriesWidget;
use App\Filament\Widgets\TotalCamerasWidget;
use App\Filament\Widgets\ActiveCamerasWidget;
use App\Filament\Widgets\InactiveCamerasWidget;
use App\Filament\Widgets\CamerasPerCountryWidget;
use App\Filament\Resources\UserResource;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            UsersWidget::class,
            CountriesWidget::class,
            TotalCamerasWidget::class,
            ActiveCamerasWidget::class,
            InactiveCamerasWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            CamerasPerCountryWidget::class,
        ];
    }
}
