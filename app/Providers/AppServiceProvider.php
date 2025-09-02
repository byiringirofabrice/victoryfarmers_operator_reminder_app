<?php

namespace App\Providers;
use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
   

public function register(): void
{
    $this->app->bind(LoginResponse::class, CustomLoginResponse::class);
}
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
