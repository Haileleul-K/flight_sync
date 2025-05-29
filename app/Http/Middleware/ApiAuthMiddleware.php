<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;



class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        Log::debug('ApiAuthMiddleware triggered', [
           $request->header('Authorization'),
            
        ]);

        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $authenticate = true;

        if (!$token) {
            $authenticate = false;
        }

        $user = User::where('token', $token)->first();
        if (!$user) {
            $authenticate = false;
        } else {
            Auth::login($user);
        }

        if ($authenticate) {
            return $next($request);
        } else {
            return response()->json([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ])->setStatusCode(401);
        }
    }
}
