<?php

namespace App\Http\Middleware;

use Closure;
use \App\Setting;

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
        // Pre-Middleware Action

        $token    = ($request->header('token')) ? $request->header('token') : false;
        if (!$token) {
          return response('Unauthrized', 401);
        }
        $setting  = Setting::where(['token' =>  $token])->first();
        if (!$setting) {
          return response('Unauthrized', 401);
        }

        $response = $next($request);
        return $response;
    }
}
