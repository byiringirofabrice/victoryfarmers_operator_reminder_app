<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;

class UsersWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // Stat::make('Total Users', User::count())
            //     ->icon('heroicon-o-users')
            //     ->description('All registered users')
            //     ->color('success'),
        ];
    }
}
