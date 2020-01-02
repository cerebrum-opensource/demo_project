<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CheckPermission
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
        if(isset(\Config::get('permissions')[Route::getFacadeRoot()->current()->getName()]))
        {
            if(!Auth::user()->hasPermissionTo(\Config::get('permissions')[Route::getFacadeRoot()->current()->getName()]))
            {
                abort(503);
                exit;
            }
        }  
        return $next($request);
    }
}
