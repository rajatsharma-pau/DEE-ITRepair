<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\StoreAccessScope;

class StoreViewAllowed
{
    public function handle($request, Closure $next)
    {
        StoreAccessScope::assertCanViewStore();
        return $next($request);
    }
}
