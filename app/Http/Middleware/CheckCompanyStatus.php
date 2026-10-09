<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->company) {
            // Si la empresa está suspendida (y el usuario no es superadmin global)
            if (Auth::user()->company->status === 'suspended' && Auth::user()->role !== 'super_admin') {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'La empresa a la que pertenece ha sido suspendida temporalmente.',
                ]);
            }
        }

        return $next($request);
    }
}