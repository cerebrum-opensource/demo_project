<?php

namespace App\Http\Middleware;

use Closure;

class PasswordExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        
        $password_changed_at = new \Carbon\Carbon($user->password_expiry);

        if (\Carbon\Carbon::now()->diffInDays($password_changed_at) >= config('auth.password_expires_days')) {
            $request->session()->put('password_expire_time', $password_changed_at);            
        }

        return $next($request);
    }
}
