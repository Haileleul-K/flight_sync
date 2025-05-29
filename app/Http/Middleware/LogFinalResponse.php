<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogFinalResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        Log::debug('Response before forcing Content-Type in LogFinalResponse', [
            'url' => $request->fullUrl(),
            'headers' => $response->headers->all(),
            'content' => $response->getContent(),
        ]);

        if ($request->is('api/*')) {
            $response->header('Content-Type', 'application/json');
        }

        Log::debug('Response after forcing Content-Type in LogFinalResponse', [
            'url' => $request->fullUrl(),
            'headers' => $response->headers->all(),
            'content' => $response->getContent(),
        ]);

        return $response;
    }

    public function terminate(Request $request, $response)
    {
        Log::debug('Final response in LogFinalResponse terminate', [
            'url' => $request->fullUrl(),
            'headers' => $response->headers->all(),
            'content' => $response->getContent(),
        ]);
    }
}
