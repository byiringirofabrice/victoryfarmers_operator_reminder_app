<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        $allowedRoles = ['super_admin', 'country_manager', 'control_room_supervisor'];

        if (! auth()->check() || ! in_array(auth()->user()->role, $allowedRoles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
