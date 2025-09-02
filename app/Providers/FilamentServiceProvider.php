<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Filament\Facades\Filament;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use App\Filament\Auth\CustomLoginResponse;
use App\Filament\Auth\CustomLogoutResponse;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginResponseContract::class, CustomLoginResponse::class);
        $this->app->bind(LogoutResponseContract::class, CustomLogoutResponse::class);
    }

    public function boot(): void
    {
        Filament::serving(function () {
            Gate::define('viewFilament', function ($user) {
                return $user->hasRole('foreman');
            });
        });
    }
}
