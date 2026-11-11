<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user || ($roles !== [] && ! in_array($user->role, $roles, true))) {
            abort(403, 'You are not allowed to perform this action.');
        }

        return $next($request);
    }
}
