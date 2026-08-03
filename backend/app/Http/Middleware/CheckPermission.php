<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Laravel passes `permission:a|b|c` as separate middleware parameters.
     * Accept any of them (OR). Also supports a single argument containing `|`.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = auth()->user();

        $allowed = [];
        foreach ($permissions as $permission) {
            foreach (explode('|', $permission) as $perm) {
                $perm = trim($perm);
                if ($perm !== '') {
                    $allowed[] = $perm;
                }
            }
        }

        foreach ($allowed as $perm) {
            if ($user->hasPermission($perm)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized');
    }
}
