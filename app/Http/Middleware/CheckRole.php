<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if(!$user || !$user->role){
            abort(403, 'Unauthorized');
        }

        if(in_array($user->role->name, $roles)){
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
