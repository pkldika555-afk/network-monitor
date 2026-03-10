<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check())
            return redirect()->route('services.index');
        return view('auth.login');
    }
    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required'
        ]);
        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nrp';
        if (Auth::attempt([$field => $login, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('services.index'));
        }
        return back()->withErrors([
            'login' => 'NRP / Email atau Password salah',
        ])->onlyInput('login');
    }
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
