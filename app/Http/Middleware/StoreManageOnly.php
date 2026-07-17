<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\StoreAccessScope;

class StoreManageOnly
{
    public function handle($request, Closure $next)
    {
        StoreAccessScope::assertCanManageStore();
        return $next($request);
    }
}
