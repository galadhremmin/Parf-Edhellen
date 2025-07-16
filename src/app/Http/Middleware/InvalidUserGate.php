<?php

namespace App\Http\Middleware;

use App\Security\RoleConstants;
use Closure;
use Illuminate\Http\Request;

class InvalidUserGate
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user !== null && ! $user->isRoot()) {
            $isBanned = $user->is_deleted || //
                ! $user->roles()->where('name', RoleConstants::Users)->exists();
            
            if ($isBanned) {
                return response('', 403);
            }
        }

        return $next($request);
    }
}
