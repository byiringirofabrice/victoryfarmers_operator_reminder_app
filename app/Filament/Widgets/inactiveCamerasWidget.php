<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Camera;

class InactiveCamerasWidget extends BaseWidget
{
    // protected function getStats(): array
    // {
    //     // return [
    //     //     Stat::make('Online Cameras', Camera::where('is_online','1')->count())
    //     //         ->icon('heroicon-o-check-circle')
    //     //         ->description('Currently online')
    //     //         ->color('success'),
    //     // ];
    //     // return [
    //     //     Stat::make('Inactive Cameras', Camera::where('is_active', '0')->count())
    //     //         ->icon('heroicon-o-x-circle')
    //     //         ->description('Currently offline')
    //     //         ->color('danger'),
    //     // ];
    // }
}