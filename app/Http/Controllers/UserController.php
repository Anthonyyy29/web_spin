<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt($request->only('username', 'password'))) {
            $request->session()->regenerate();
            $area = Auth::user()->area();

            if ($area === null) {
                return redirect()->route('unavailable');
            }

            return redirect()->intended(route($area->homeRoute()));
        }

        return back()->withErrors(['username' => 'Username atau password salah.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
