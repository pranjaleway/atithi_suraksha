<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $menuSlug, $action = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        if (!hasPermission($menuSlug, $action)) {
            return abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
