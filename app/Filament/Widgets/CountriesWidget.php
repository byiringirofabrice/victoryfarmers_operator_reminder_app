<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Camera;
use App\Models\Country;
use App\Models\Site;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CountriesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $countries = Country::count();
        $users = User::count();
        $branches = Branch::count();
        $sites = Site::count();
        $cameras = Camera::count();

        return [
            Stat::make('Countries', number_format($countries))
                ->icon('heroicon-o-globe-alt')
                ->description('Global presence')
                ->color('info')
                ->chart($this->generateChartData($countries, 50))
                ->extraAttributes([
                    'class' => 'hover:scale-105 transition-transform duration-200 cursor-pointer group'
                ]),
            
            Stat::make('Users', number_format($users))
                ->icon('heroicon-o-user-group')
                ->description('Active team members')
                ->color('success')
                ->chart($this->generateChartData($users, 100))
                ->descriptionIcon($users > 10 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->extraAttributes([
                    'class' => 'hover:scale-105 transition-transform duration-200 cursor-pointer group'
                ]),
            
            Stat::make('Branches', number_format($branches))
                ->icon('heroicon-o-building-storefront')
                ->description('Operational locations')
                ->color('primary')
                ->chart($this->generateChartData($branches, 30))
                ->extraAttributes([
                    'class' => 'hover:scale-105 transition-transform duration-200 cursor-pointer group'
                ]),
            
            Stat::make('Sites', number_format($sites))
                ->icon('heroicon-o-building-office-2')
                ->description('Monitoring locations')
                ->color('warning')
                ->chart($this->generateChartData($sites, 100))
                ->extraAttributes([
                    'class' => 'hover:scale-105 transition-transform duration-200 cursor-pointer group'
                ]),
            
            Stat::make('Cameras', number_format($cameras))
                ->icon('heroicon-o-video-camera')
                ->description('Total surveillance devices')
                ->color('danger')
                ->chart($this->generateChartData($cameras, 200))
                ->descriptionIcon($cameras > 50 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->extraAttributes([
                    'class' => 'hover:scale-105 transition-transform duration-200 cursor-pointer group'
                ]),
        ];
    }

    private function generateChartData($value, $max = 100): array
    {
        if ($value === 0) {
            return [0, 0, 0, 0, 0];
        }

        $percentage = min(100, ($value / $max) * 100);
        $fluctuation = rand(-15, 15);

        return [
            max(0, $percentage - 30 + $fluctuation),
            max(0, $percentage - 15 + $fluctuation),
            $percentage + $fluctuation,
            min(100, $percentage + 15 + $fluctuation),
            min(100, $percentage + 30 + $fluctuation),
        ];
    }

    protected function getHeading(): string
    {
        return 'Operations Overview';
    }

    // Optional: Add polling for live updates
    public function getPollingInterval(): ?string
    {
        return '60s'; // Update every minute
    }
}