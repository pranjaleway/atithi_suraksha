<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $menuName, $action = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        if (!hasPermission($menuName, $action)) {
            return abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
