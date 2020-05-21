<?php

namespace App\Packages\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;

class TelescopeAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $access = env('TELESCOPE_KEY', 'lettbee');

        $cookie = $request->cookie('__telescope__');

        if ($cookie && $cookie === $access) {
            return $next($request);
        }

        if ($request->code && $request->code === $access) {
            $cookie = $request->code;
            Cookie::queue('__telescope__', $cookie, 60 * 24 * 7, '/');

            return $next($request);
        }

        return response('permission denied', 404);
    }
}
