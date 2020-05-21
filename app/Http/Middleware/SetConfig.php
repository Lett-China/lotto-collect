<?php

namespace App\Http\Middleware;

use App;
use Closure;

// use Illuminate\Support\Facades\Route;

class SetConfig
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
        $allow = ['zh_CN', 'en'];
        $lang  = $request->header('Accept-Language-App', 'zh_CN');
        $lang  = in_array($lang, $allow) ? $lang : 'zh_CN';
        App::setLocale($lang);

        return $next($request);
    }
}
