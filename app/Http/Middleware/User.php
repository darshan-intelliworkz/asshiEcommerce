<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class User
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
        // if(empty(session('user'))){
        //     return redirect()->route('login.form');
        // }
        // else{
        //     return $next($request);
        // }

        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }
        // Check if the logged-in user's role is 'user'
        if (Auth::user()->role !== 'user') {
            return redirect()->route('login.form');
        }

        return $next($request);
    }
}
