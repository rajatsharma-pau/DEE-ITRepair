<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && !in_array($request->route()->getName(), ['password.change', 'password.change.update', 'logout'])) {
            if (!empty(Auth::user()->must_change_password)) {
                return redirect()->route('password.change')->with('warning', 'Please change your default password before continuing.');
            }
        }

        return $next($request);
    }
}
