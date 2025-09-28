<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        $token = $request->header('api-token');

        if ($token) {
            $request->query->set('api_token', $token);
        }


        if ($this->auth->guard($guard)->guest()) {
            return response('Unauthorized..', 401);
        }
        if(!$this->auth->guard($guard)->user()->usuario_id){
            return response('Incorrect Area.', 401);
        }

        return $next($request);
    }
}
