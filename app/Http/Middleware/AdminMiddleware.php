<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminMiddleware
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
        //$user = User::all()->count();
        if (!Auth::user()->hasRole('Admin')) //If user does //not have this permission
        {
            abort('401');
            exit;
        }
        
        return $next($request);
    }
}