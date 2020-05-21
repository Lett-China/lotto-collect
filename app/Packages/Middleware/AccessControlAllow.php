<?php
namespace App\Packages\Middleware;

use Closure;

class AccessControlAllow
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
        if ($request->isMethod('OPTIONS')) {
            $response = response('ok', 200);
        } else {
            $response = $next($request);
        }

        $IlluminateResponse     = 'Illuminate\Http\Response';
        $IlluminateJsonResponse = '\Illuminate\Http\JsonResponse';
        $SymfonyResponse        = 'Symfony\Component\HttpFoundation\Response';

        $origin  = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        $headers = [
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Methods'     => 'HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => $request->header('Access-Control-Request-Headers'),
            'Access-Control-Allow-Credentials' => 'true',
        ];

        $cache                              = $response->original['cache'] ?? 0;
        $cache && $headers['Cache-Control'] = 'max-age=' . $cache;

        if ($response instanceof $IlluminateJsonResponse) {
            $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            $response->withHeaders($headers);
        }

        if ($response instanceof $IlluminateResponse) {
            $response->withHeaders($headers);
        }

        if ($response instanceof $SymfonyResponse) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
