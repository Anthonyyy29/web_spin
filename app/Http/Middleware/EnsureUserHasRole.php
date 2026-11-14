<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Izinkan permintaan hanya bila peran pengguna ada di daftar.
     *
     * Membandingkan nilai mentah kolom agar nilai tak dikenal tidak memicu error cast enum.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! in_array($user->getRawOriginal('role'), $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
