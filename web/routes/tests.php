<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
*/

/*-------------------------
   T E S T S
-------------------------- */

Route::group(
    [
        'prefix' => env('APP_API_PREFIX') . '/tests'
    ],
    function ($router) {
        $router->get('db-test', function () {
            if (DB::connection()->getDatabaseName()) {
                echo "Connected successfully to database: " . DB::connection()->getDatabaseName();
            }
        });

        $router->get('contacts', 'PagesController@index');

        /**
         *  For test
         */
        $router->get('run', '\App\Services\Import@run');
        $router->post('test', '\App\Services\Import@exec');

        $router->get('run2', '\App\Services\CsvParser@run');
        $router->post('test2', '\App\Services\CsvParser@test');
    }
);
