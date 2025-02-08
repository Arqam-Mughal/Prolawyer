<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckNoPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd('data');
        if(Auth::check()){
        $permissions = Auth::user()->getAllPermissions()->toArray();

        if(empty($permissions)){
            session()->flash('permission_error', 'You have no permissions assigned.');
        }
       }

        return $next($request);
    }
}
