<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $u = $request->user();
        if (!$u) abort(403);

        $role = strtolower($role);
        $userRole = strtolower((string)($u->Role ?? ''));

        // admin bypass
        if ($role !== 'admin' && method_exists($u, 'isAdmin') && $u->isAdmin()) {
            return $next($request);
        }

        if ($userRole !== $role) abort(403);

        return $next($request);
    }
}
