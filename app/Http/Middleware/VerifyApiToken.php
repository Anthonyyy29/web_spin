<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    /**
     * Izinkan hanya permintaan dengan header Authorization sama dengan token API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->header('Authorization');

        if ($header === '') {
            return response()->json(['status' => false, 'message' => 'Authorization token missing'], 401);
        }

        $token = (string) config('services.podcast_api.token');

        if ($token === '' || ! hash_equals($token, $header)) {
            return response()->json(['status' => false, 'message' => 'Invalid token'], 403);
        }

        return $next($request);
    }
}
