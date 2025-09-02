<?php

namespace App\Filament\Auth;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect('/login'); // ← Redirect anywhere you want after logout
    }
}
