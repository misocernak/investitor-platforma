<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function prikaziFormu()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('zapamti'))) {
            $request->session()->regenerate();
            if (Auth::user()->status_naloga !== 'Aktivan') {
                Auth::logout();
                return back()->withErrors(['email' => 'Nalog nije aktivan.']);
            }
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Pogrešan email ili lozinka.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
