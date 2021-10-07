<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () use ($router) {
    return $router->app->version();
});

Route::group(
    [
        'prefix' => env('APP_API_PREFIX', '')
    ],
    function ($router) {
        include base_path('app/Api/V1/routes.php');
    }
);

if (file_exists(__DIR__ . '/tests.php'))
    require_once(__DIR__ . '/tests.php');
