<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return response()->view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
   public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = Auth::user()->fresh(); // Ensure latest role info from DB

    // Debug (optional)
    // \Log::info("Logged in as {$user->email}, roles: " . $user->getRoleNames());

    if ($user->hasRole('foreman')) {
        return redirect(Filament::getUrl()); // Redirect to /admin
    } elseif ($user->hasRole('operator')) {
        return redirect()->route('operator.index'); // Your custom route
    }

    return redirect('dashboard'); // Fallback route
}
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Logged out successfully.');
    }
}