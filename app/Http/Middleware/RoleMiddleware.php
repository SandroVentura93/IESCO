<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');
        // allow multiple roles separated by |
        $roles = explode('|', $role);
        if (!in_array($user->role, $roles)) abort(403);

        return $next($request);
    }
}
