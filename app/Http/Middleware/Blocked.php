<?php
namespace App\Http\Middleware;

use Closure;
use \App\Blocked as Block;

class Blocked
{
    public function handle($request, Closure $next)
    {
      if (Block::where('ip', $_SERVER['REMOTE_ADDR'])->get()->first()) {
        return response()->json('Denied', 401);
      }
      return $next($request);
    }
}
