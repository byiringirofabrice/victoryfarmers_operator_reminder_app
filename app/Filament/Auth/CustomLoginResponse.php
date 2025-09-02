<?php

namespace App\Filament\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = auth()->user();

        // Redirect based on user role
        if ($user->role === 'foreman') {
            return redirect()->intended('/admin');
        } elseif ($user->role === 'operator') {
            return redirect()->intended('/operator');
        }

        // Default fallback
        return redirect()->intended('/');
    }
}
