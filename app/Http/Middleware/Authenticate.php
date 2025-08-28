<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            
            if ($request->getHost() == 'admin.famoryapp.com') {
                return route('admin.login');
            }
            
        // Handle domain-based redirection if not authenticated
            if ($request->getHost() == 'partners.famoryapp.com') {
                return route('login');
            }
        }
    }
}
