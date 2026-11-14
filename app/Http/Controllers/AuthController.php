<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan form login, atau alihkan ke dashboard bila sudah login.
     */
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard')->with('success', 'login berhasil');
        }

        return view('login');
    }
}
