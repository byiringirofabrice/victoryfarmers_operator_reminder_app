<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('foreman')) {
            return redirect()->intended('/admin');
        }

        if ($user->hasRole('operator')) {
            return redirect()->intended('/operator');
        }

        // fallback
        return redirect('/');
    }
}
