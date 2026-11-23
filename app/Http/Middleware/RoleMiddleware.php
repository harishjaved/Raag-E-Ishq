<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            abort(403, 'User account is inactive or unauthenticated.');
        }

        $authorized = match ($permission) {
            'scan' => $user->canScan(),
            'admin' => $user->canAccessAdmin(),
            'finance' => $user->canManageFinancials(),
            'super_admin' => $user->isSuperAdmin(),
            default => false,
        };

        if (!$authorized) {
            abort(403, 'Unauthorized. Access is restricted for your role.');
        }

        return $next($request);
    }
}
