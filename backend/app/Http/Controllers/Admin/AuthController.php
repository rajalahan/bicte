<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('admin.dashboard');
        return view('admin.auth.login');
    }

    public function login(Request $r)
    {
        $cred = $r->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!Auth::attempt($cred, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'इमेल वा पासवर्ड मेल खाएन।'])->onlyInput('email');
        }

        $r->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
