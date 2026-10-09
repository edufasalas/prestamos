<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // 1. Validar si el usuario está inactivo
        // 2. Validar si la empresa está suspendida o inactiva
        if ($user->status !== 'active' || ($user->company && $user->company->status !== 'active')) {
            Auth::logout();
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Su cuenta o su empresa se encuentran suspendidas. Contacte a soporte.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.leads.metrics'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}