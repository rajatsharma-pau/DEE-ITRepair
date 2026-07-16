<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
class DeeRoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::guard('dee')->user();
        if (!$user || !in_array($user->role, $roles)) abort(403, 'Unauthorized');
        return $next($request);
    }
}
