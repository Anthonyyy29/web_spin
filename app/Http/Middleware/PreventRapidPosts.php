<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventRapidPosts
{
    /**
     * Tolak POST beruntun dari session yang sama dalam kurang dari satu detik.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST')) {
            $now = now()->getPreciseTimestamp(3);
            $last = $request->session()->get('saveProgress');

            if ($last !== null && $now - $last < 1000) {
                return back()->with('error', 'Too many actions');
            }

            $request->session()->put('saveProgress', $now);
        }

        return $next($request);
    }
}
