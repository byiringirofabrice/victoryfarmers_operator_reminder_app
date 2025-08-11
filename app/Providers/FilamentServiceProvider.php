<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;

use Illuminate\Support\Facades\Gate;
class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        Filament::serving(function () {
            Gate::define('viewFilament', function ($user) {
                return $user->hasRole('foreman');
            });
        });
    }
}
