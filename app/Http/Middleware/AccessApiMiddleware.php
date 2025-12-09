<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // print_r($request); exit;
        if($request->server('REQUEST_URI') === '/api/login'){
            if( empty($request->server('HTTP_AUTHORIZATION')) && $request->server('REQUEST_URI') !== '/api/login'){
                return response()->json([
                    'message'=>'please login to authorize'
                ], 403);
            }
            // print_r($request);
            if(!empty($request->server('HTTP_AUTHORIZATION')) && !$request->user()){
                return response()->json([
                    'message'=>'Unauthorized access. check token'
                ], 403);
            }
        }

        if($request->user() && $request->user()->isAdmin() == 1){
            return response()->json([
                'message'=>'Unauthorized access'
            ], 403);
        }
        return $next($request);
    }
}
