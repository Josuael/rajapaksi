<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * ✅ login pakai login_id + password (M_User)
     */
    public function store(Request $request)
    {
        $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('login_id', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'))
                ->with('toast', [
                    'type' => 'success',
                    'title' => 'Login berhasil',
                    'text' => 'Welcome back!'
                ]);
        }

        return back()
            ->with('toast', [
                'type' => 'error',
                'title' => 'Login gagal',
                'text' => 'Username / Password salah.'
            ])
            ->withErrors([
                'login_id' => 'These credentials do not match our records.',
            ])
            ->onlyInput('login_id');
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
