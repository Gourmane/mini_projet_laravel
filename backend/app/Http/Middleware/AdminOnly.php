<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(
            $request->user()?->role === 'admin',
            403,
            'Cette action est réservée aux administrateurs.',
        );

        return $next($request);
    }
}

