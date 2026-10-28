<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = 'login|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return back()->withErrors(['email' => 'Demasiados intentos. Intenta de nuevo más tarde.']);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            // Redirect based on role
            $user = Auth::user();
            $role = $user->role ?? 'alumno';
            if ($role === 'admin') {
                return redirect()->intended('/admin');
            }
            if ($role === 'director') {
                return redirect()->intended('/director');
            }
            if ($role === 'docente') {
                return redirect()->intended('/teacher');
            }

            return redirect()->intended('/student');
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors(['email' => 'Las credenciales no son correctas.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
