<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Closure;

class AddCorsHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        //if($request->path() !='/' && $request->path() !='/login'){

            $http_origin =  $request->server('HTTP_ORIGIN') ;
	        $response->header('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, DELETE,OPTIONS');
            $response->header('Access-Control-Allow-Headers', $request->header('Access-Control-Request-Headers'));
	        $response->header('Access-Control-Allow-Origin',$http_origin);
	        $response->header('Access-Control-Allow-Credentials', 'true');

	        //   }

        return $response;
    }
}
