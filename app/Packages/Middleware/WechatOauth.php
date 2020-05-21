<?php

namespace App\Packages\Middleware;

use Closure;
use EasyWeChat;
use Illuminate\Http\Request;

class WechatOauth
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
        $token = $request->cookie('__le_token__');
        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
            $user = auth($guard)->user();

            if ($user) {
                return $next($request);
            }
        }

        $redirect = $request->getUri();
        $cache    = ['redirect' => $redirect];

        $app   = EasyWeChat::officialAccount();
        $oauth = $app->oauth;
        $url   = $oauth->redirect()->getTargetUrl();

        $temp  = parse_url($url);
        $temp  = parse_str($temp['query'], $params);
        $state = $params['state'];

        cache()->put($state, $cache, 300);

        return redirect($url);
    }
}
