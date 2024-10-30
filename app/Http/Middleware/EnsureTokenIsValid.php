<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $authorization = $request->header('Authorization');
            if (!isset($authorization) && $request->has('authtoken')) {
                $token = $request->get('authtoken');
                $request->headers->set('Authorization', "Bearer $token");
            }
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            $exceptions = [
                TokenInvalidException::class => ['message' => 'Invalid Token!', 'status' => 401],
                TokenExpiredException::class => ['message' => 'Token expired', 'status' => 422],
            ];

            foreach ($exceptions as $exception => $response) {
                if ($e instanceof $exception) {
                    return response()->json(['message' => $response['message']], $response['status']);
                }
            }

            // Default response
            return response()->json(['message' => 'Token not found.'], 400);
        }

        return $next($request);
    }
}
