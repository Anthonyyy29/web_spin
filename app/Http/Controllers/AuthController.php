<?php

namespace App\Http\Controllers;

use App\Models\AdminHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

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

    /**
     * Proses login dengan batas lima kegagalan per IP dalam 60 detik.
     */
    public function login(Request $request): RedirectResponse
    {
        $kunci = 'login|'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            return redirect('/login')->with('error', 'Terlalu banyak percobaan. Coba lagi nanti.');
        }

        $username = $request->input('username');
        $password = $request->input('password');

        $berhasil = Auth::attempt([
            'username' => is_string($username) ? $username : '',
            'password' => is_string($password) ? $password : '',
        ]);

        if ($berhasil) {
            $request->session()->regenerate();
            RateLimiter::clear($kunci);
            AdminHistory::recordLogin($request);

            return redirect('/dashboard')->with('success', 'login berhasil');
        }

        RateLimiter::hit($kunci, 60);

        return redirect('/login')->with('error', 'login gagal');
    }

    /**
     * Akhiri sesi login lalu kembali ke halaman login.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah logout.');
    }
}
