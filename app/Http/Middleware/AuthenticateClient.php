<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthenticateClient
{
    public function handle(\Illuminate\Http\Request $request, \Closure $next)
    {
        // 1) Get token (Bearer, header, or query)
        $token = $request->bearerToken()
            ?: $request->header('api-token')
                ?: $request->query('api_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized (missing token)'], 401);
        }

        // 2) Lookup user
        $user = User::where('api_token', $token)->first();
        if (!$user) {
            Log::warning('authCliente: invalid token', ['suffix' => substr($token, -6)]);
            return response()->json(['message' => 'Unauthorized (invalid token)'], 401);
        }

        // 3) Attach to auth context
        Auth::setUser($user);
        $request->setUserResolver(fn() => $user);

        // 4) Optional: area check
        if (!$user->cliente_id) {
            return response()->json(['message' => 'Incorrect Area.'], 401);
        }

        return $next($request);
    }
}
