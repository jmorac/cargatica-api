<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogResponseTime {

	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 *
	 * @return mixed
	 */
	public function handle( Request $request, Closure $next )
	{
		$response = $next( $request );

		$this->logResponseTime( $request, $response );

		return $response;
	}

	private function logResponseTime( Request $request, $response )
	{

        $excludeDir=["/api/v1/send-message"];

        if(!in_array($request->getRequestUri(),$excludeDir) ) {

            if (\defined('LARAVEL_START')) {
                $timeToProcess = round(microtime(true) - LARAVEL_START, 4);

                if (env('DEBUG_REQUEST_RESPONSE', false) && method_exists($response, 'content')) {
                    Log::debug('[Middleware-LogResponseTime]' . ($timeToProcess > 3 ? ' [SLOW] ' : '') . '[' . $timeToProcess . 's] - ' . $request->getMethod() . ' - ' . $request->getRequestUri() . ' -input- ' . json_encode($request->all()) . ' -output- ' . $response->content());
                } else {
                    Log::debug('[Middleware-LogResponseTime]' . ($timeToProcess > 3 ? ' [SLOW] ' : '') . '[' . $timeToProcess . 's] - ' . $request->getMethod() . ' - ' . $request->getRequestUri() . ' - ' . json_encode($request->all()));
                }
            } else {
                Log::error('[Middleware-LogResponseTime] ' . $request->getRequestUri() . ' time could not be determined.');
            }
        }
	}
}
