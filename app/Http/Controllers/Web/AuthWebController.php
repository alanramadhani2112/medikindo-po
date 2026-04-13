<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('web.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Gate: reject deactivated accounts
            if (! Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
            }

            return redirect()->route('web.dashboard');
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->with('error', 'Email atau password tidak valid.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
