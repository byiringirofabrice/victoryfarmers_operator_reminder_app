<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, [
            'super_admin',
            'country_manager',
            'control_room_supervisor',
        ])) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}