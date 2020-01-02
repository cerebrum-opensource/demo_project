<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
              $user = Auth::user();
              if($user->hasRole('Admin'))
              {
                return redirect('/admin/users');
              }
              elseif($user->hasRole('CHW'))
              {
                return redirect('/chw/patients/registrations');
              }
              elseif($user->hasRole('MD'))
              {
                return redirect('/md/patients/registrations');
              }
              else
              {
                return redirect('/casemanager/patients');
              }
        }

        return $next($request);
    }
}
