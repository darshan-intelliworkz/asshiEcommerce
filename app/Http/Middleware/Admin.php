<?php

namespace App\Http\Middleware;

use Closure;

class Admin
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
        if($request->user()->role=='admin'){
            return $next($request);
        }
        else{
            request()->session()->flash('error','Please login again as another user has logged in to the same browser');
            return redirect()->route('admin.login');
            //return redirect()->route($request->user()->role);
        }
    }
}
