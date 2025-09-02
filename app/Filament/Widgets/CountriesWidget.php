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
        return [
            Stat::make('Countries', Country::count())
                ->icon('heroicon-o-globe-alt')
                ->description('Where cameras are installed')
                ->color('info'),
                Stat::make('Total Users', User::count())
                ->icon('heroicon-o-users')
                ->description('All registered users')
                ->color('success'),
                 Stat::make('Total Branches', Branch::count())
                ->icon('heroicon-o-users')
                ->description('All registered branches')
                ->color('primary'),
                Stat::make('Total sites', Site::count())
                ->icon('heroicon-o-building-office')
                ->description('All registered sites')
                ->color('warning'),
        ];
     
    }
}
