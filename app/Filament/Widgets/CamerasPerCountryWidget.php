<?php

namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Models\Country;
use Illuminate\Support\Collection;

class CamerasPerCountryWidget extends BarChartWidget
{
    protected static ?string $heading = '📊 Camera Distribution by Country';
    protected static ?string $maxHeight = '400px';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $countries = Country::withCount('cameras')
            ->having('cameras_count', '>', 0)
            ->orderBy('cameras_count', 'desc')
            ->get();

        if ($countries->isEmpty()) {
            return $this->getEmptyData();
        }

        $colors = $this->generateColors($countries->count());
        $gradientColors = $this->generateGradientColors($countries->count());

        return [
            'datasets' => [
                [
                    'label' => 'Number of Cameras',
                    'data' => $countries->pluck('cameras_count')->toArray(),
                    'backgroundColor' => $gradientColors,
                    'borderColor' => $colors,
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'hoverBackgroundColor' => array_map(fn($color) => str_replace('0.8', '1', $color), $gradientColors),
                    'hoverBorderColor' => '#000',
                    'hoverBorderWidth' => 3,
                ],
            ],
            'labels' => $countries->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'color' => '#374151',
                        'font' => [
                            'size' => 14,
                            'weight' => '600',
                            'family' => "'Inter', 'system-ui', 'sans-serif'"
                        ]
                    ]
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => '#fff',
                    'bodyColor' => '#fff',
                    'borderColor' => '#000',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'callbacks' => [
                        'label' => function($context) {
                            return $context->dataset->label . ': ' . $context->parsed->y;
                        }
                    ]
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)'
                    ],
                    'ticks' => [
                        'color' => '#6B7280',
                        'font' => [
                            'size' => 12,
                            'weight' => '500'
                        ]
                    ]
                ],
                'x' => [
                    'grid' => [
                        'display' => false
                    ],
                    'ticks' => [
                        'color' => '#374151',
                        'font' => [
                            'size' => 11,
                            'weight' => '600'
                        ],
                        'maxRotation' => 45,
                        'minRotation' => 45
                    ]
                ]
            ],
            'animation' => [
                'duration' => 2000,
                'easing' => 'easeOutQuart'
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index'
            ]
        ];
    }

    private function generateColors(int $count): array
    {
        $baseColors = [
            'rgba(54, 162, 235, 1)',    // Blue
            'rgba(255, 99, 132, 1)',    // Pink
            'rgba(75, 192, 192, 1)',    // Teal
            'rgba(255, 159, 64, 1)',    // Orange
            'rgba(153, 102, 255, 1)',   // Purple
            'rgba(255, 205, 86, 1)',    // Yellow
            'rgba(201, 203, 207, 1)',   // Gray
            'rgba(0, 168, 107, 1)',     // Green
            'rgba(231, 76, 60, 1)',     // Red
            'rgba(155, 89, 182, 1)',    // Deep Purple
        ];

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return $colors;
    }

    private function generateGradientColors(int $count): array
    {
        $baseGradients = [
            'rgba(54, 162, 235, 0.8)',    // Blue
            'rgba(255, 99, 132, 0.8)',    // Pink
            'rgba(75, 192, 192, 0.8)',    // Teal
            'rgba(255, 159, 64, 0.8)',    // Orange
            'rgba(153, 102, 255, 0.8)',   // Purple
            'rgba(255, 205, 86, 0.8)',    // Yellow
            'rgba(201, 203, 207, 0.8)',   // Gray
            'rgba(0, 168, 107, 0.8)',     // Green
            'rgba(231, 76, 60, 0.8)',     // Red
            'rgba(155, 89, 182, 0.8)',    // Deep Purple
        ];

        $gradients = [];
        for ($i = 0; $i < $count; $i++) {
            $gradients[] = $baseGradients[$i % count($baseGradients)];
        }

        return $gradients;
    }

    private function getEmptyData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'No Data Available',
                    'data' => [1],
                    'backgroundColor' => 'rgba(200, 200, 200, 0.2)',
                    'borderColor' => 'rgba(200, 200, 200, 0.5)',
                ],
            ],
            'labels' => ['No cameras found'],
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '120s'; // Refresh every 2 minutes
    }

    public static function canView(): bool
    {
        return Country::has('cameras')->exists();
    }

    public function getDescription(): ?string
    {
        return 'Distribution of surveillance cameras across different countries';
    }
}