<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


class Check2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    
    public function handle(Request $request, Closure $next)
    {
        // if (!Session::has('token')) {
        //     return response()->json([
        //         'status' => 'error',
        //         'token' => 'token is required',
        //         'data' => null

        //     ], 401);
        // }

        // try {
        //     if (!$user = JWTAuth::parseToken()->authenticate()) {
        //         return response()->json(['error' => 'User not found'], 404);
        //     }
        // } catch (TokenExpiredException $e) {
        //     return response()->json(['token_expired'], $e->getStatusCode());
        // } catch (TokenInvalidException $e) {
        //     return response()->json(['token_invalid'], $e->getStatusCode());
        // } catch (JWTException $e) {
        //     return response()->json(['token_absent' => 'A token is required'], $e->getStatusCode());
        // }
        
        // $user = Auth::user();
        // if (!$user ){
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'session not active'
        //         ], 401);
        //         }
        return $next($request);
    }
}
