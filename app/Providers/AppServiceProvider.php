<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        JsonResponse::macro('ensureJsonContentType', function () {
            return $this->header('Content-Type', 'application/json');
        });

        \Illuminate\Support\Facades\Response::macro('json', function ($data = [], $status = 200, array $headers = [], $options = 0) {
            $headers['Content-Type'] = 'application/json';
            return (new JsonResponse($data, $status, $headers, $options))->ensureJsonContentType();
        });
    }
}
