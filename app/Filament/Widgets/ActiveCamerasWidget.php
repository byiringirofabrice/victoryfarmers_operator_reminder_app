<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Camera;

class ActiveCamerasWidget extends BaseWidget
{
    // protected function getStats(): array
    // {
    //     // return [
    //     //     Stat::make('Active Cameras', Camera::where('is_active')->count())
    //     //         ->icon('heroicon-o-check-circle')
    //     //         ->description('Currently active')
    //     //         ->color('success'),
    //     // ];
    // }
}
