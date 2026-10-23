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
            if (Auth::user()->role === 'hr') {
                return redirect()->intended(route('hr.dashboard'));
                } elseif (Auth::user()->role === 'announcer') {
                    return redirect()->intended(route('announcer.dashboard'));
                } elseif (Auth::user()->role === 'admin') {
                        return redirect()->intended(route('admin.dashboard'));
                } else {
                return redirect()->intended('/');
            }
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

