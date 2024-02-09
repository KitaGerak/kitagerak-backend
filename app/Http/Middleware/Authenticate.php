<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    // CHANGED DUE TO LARAVEL VERSION
    // protected function redirectTo(Request $request): ?string
    protected function redirectTo($request)
    {
        // CHANGED DUE TO LARAVEL VERSION
        // return $request->expectsJson() ? null : route('login');
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
