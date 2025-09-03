<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Camera;

class TotalCamerasWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCameras = Camera::count();
        $activeCameras = Camera::where('is_active', true)->count();
        $onlineCameras = Camera::where('is_online', true)->count();
        
        $activePercentage = $totalCameras > 0 ? round(($activeCameras / $totalCameras) * 100) : 0;
        $onlinePercentage = $totalCameras > 0 ? round(($onlineCameras / $totalCameras) * 100) : 0;

        return [
            Stat::make('Total Cameras', number_format($totalCameras))
                ->icon('heroicon-o-camera')
                ->description('All surveillance devices')
                ->color('gray')
                ->chart($this->getChartData($totalCameras))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow'
                ]),
            
            Stat::make('Active Cameras', number_format($activeCameras))
                ->icon('heroicon-o-check-badge')
                ->description("{$activePercentage}% of total")
                ->descriptionIcon($activePercentage > 80 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($activePercentage > 80 ? 'success' : 'warning')
                ->chart($this->getChartData($activeCameras))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow'
                ]),
            
            Stat::make('Online Now', number_format($onlineCameras))
                ->icon('heroicon-o-wifi')
                ->description("{$onlinePercentage}% connectivity")
                ->descriptionIcon($onlinePercentage > 90 ? 'heroicon-o-signal' : 'heroicon-o-signal-slash')
                ->color($onlinePercentage > 90 ? 'success' : 'danger')
                ->chart($this->getChartData($onlineCameras))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow'
                ]),
        ];
    }

    private function getChartData($value): array
    {
        // Generate some random data for the chart
        return [
            rand($value - 20, $value - 10),
            rand($value - 10, $value - 5),
            $value,
            rand($value + 5, $value + 10),
            rand($value + 10, $value + 20),
        ];
    }

    // Optional: Set polling interval without conflicting with parent
    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}