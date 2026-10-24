<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Allow the request only for a signed-in user whose role name is one of the given names.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roleNames): Response
    {
        $user = $request->user();

        if ($user === null || ! in_array($user->role?->name, $roleNames, true)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
