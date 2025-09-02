<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Camera;

class TotalCamerasWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Cameras', Camera::count())
                ->icon('heroicon-o-camera')
                ->description('All camera devices')
                ->color('primary'),
            Stat::make('Active Cameras', Camera::where('is_active', true)->count())
                ->icon('heroicon-o-check-circle')
                ->description('Currently active cameras')
                ->color('success'),
            Stat::make('Inactive Cameras', Camera::where('is_active', false)->count())
                 ->icon('heroicon-o-x-circle')
                    ->description('Currently inactive cameras')
                    ->color('danger'),
            Stat::make('Online Cameras', Camera::where('is_online', true)->count())
                ->icon('heroicon-o-video-camera')
                ->description('Currently online cameras')
                ->color('success'),
            Stat::make('Offline Cameras', Camera::where('is_online', false)->count())
                ->icon('heroicon-o-video-camera-slash')
                ->description('Currently offline cameras')
                ->color('danger'),
                 

        ];
    }
}