<?php

namespace App\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

interface CustomLoginResponse
{
    public function toResponse(Request $request): RedirectResponse;
}
