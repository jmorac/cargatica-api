<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class HttpsProtocolMiddleware
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
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
            $_SERVER['HTTPS'] = 'on';
        }
/*
        if (Request::server('HTTP_X_FORWARDED_PROTO') == 'https')
        {
            echo "secure";
        } else {
            echo "unsecure";
        }
        exit;


        if (!$request->secure() ) {
           return redirect()->secure($request->getRequestUri());
        }
*/

        return $next($request);
    }
}