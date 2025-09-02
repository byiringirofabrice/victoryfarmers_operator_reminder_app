<?php 
namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Models\Country;

class CamerasPerCountryWidget extends BarChartWidget
{
    protected static ?string $heading = 'Cameras per Country';

    protected function getData(): array
    {
        $countries = Country::withCount('cameras')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Cameras',
                    'data' => $countries->pluck('cameras_count')->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)', // Optional styling
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $countries->pluck('name')->toArray(),
        ];
    }
}
