<?php

/*-------------------------
   T E S T S  Routes
-------------------------- */

Route::group(
    [
        'prefix' => 'tests'
    ],
    function ($router) {
        $router->get('db-test', function () {
            if (DB::connection()->getDatabaseName()) {
                echo "Connected successfully to database: " . DB::connection()->getDatabaseName();
            }
        });
    }
);
