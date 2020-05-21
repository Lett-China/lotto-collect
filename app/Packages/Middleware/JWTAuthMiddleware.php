<?php

namespace App\Packages\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class JWTAuthMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard)
    {
        $user = auth($guard)->user();
        $user || real()->code(401)->exception();
        if ($user->disable == 1) {
            real()->code(401)->exception();
        }
        return $next($request);

        // ------------以下为待开发--------------
        $auth = auth($guard);
        // if ($auth->check()) {
        //     return $next($request);
        // }

        try {
            $token = $auth->refresh();
            // return $this->setAuthenticationHeader($next($request), $token);
        } catch (TokenExpiredException $error) {
            return response()->json('refresh 过期...');
        } catch (TokenBlacklistedException $error) {
            return response()->json('blacklist ');
        }

        $data = [
            'user'    => $auth->user(),
            'check'   => $auth->check(),
            'refresh' => $token,

        ];

        return response()->json($data);
    }
}
