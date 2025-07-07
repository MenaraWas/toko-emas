<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil cabang aktif dari session
        $activeBranchId = session('active_branch_id');

        // Share ke semua view
        view()->share('activeBranchId', $activeBranchId);

        return $next($request);
    }
}
