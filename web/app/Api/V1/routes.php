<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers'
], function ($router) {
    /**
     * Internal access
     */
    $router->group([
        'middleware' => 'checkUser'
    ], function ($router) {
        /**
         * Contacts
         */
        $router->group([
            'prefix' => 'contacts',
        ], function ($router) {
            $router->get('/', 'ContactController@index');
            $router->post('/', 'ContactController@store');
            $router->get('/{id:[a-fA-F0-9\-]{36}}', 'ContactController@show');
            $router->get('/{id:[a-fA-F0-9\-]{36}}/favorite', 'ContactController@favorite');
            $router->put('/{id:[a-fA-F0-9\-]{36}}', 'ContactController@update');
            $router->delete('/{id}', 'ContactController@destroy');
            $router->post('/merge', 'ContactController@merge');
            $router->post('/join-groups', 'ContactController@joinGroups');
            $router->post('/import/file', 'ContactController@importFile');
            $router->post('/import/json', 'ContactController@importJson');
        });

        /**
         * Contacts Categories
         */
        $router->group([
            'prefix' => 'categories',
        ], function ($router) {
            $router->get('/', 'CategoryController');
        });

        /**
         * Contacts Groups
         */
        $router->group([
            'prefix' => 'groups',
        ], function ($router) {
            $router->get('/', 'GroupController@index');
            $router->post('/', 'GroupController@store');
            $router->put('/{id}', 'GroupController@update');
            $router->delete('/{id}', 'GroupController@destroy');
        });

        /**
         * Contact Emails
         */
        $router->group([
            'prefix' => 'emails',
        ], function ($router) {
            $router->post('/', 'EmailController@store');
            $router->put('/{id}', 'EmailController@update');
            $router->delete('/{id}', 'EmailController@destroy');
        });

        /**
         * Contact Phones
         */
        $router->group([
            'prefix' => 'phones',
        ], function ($router) {
            $router->post('/', 'PhoneController@store');
            $router->put('/{id}', 'PhoneController@update');
            $router->delete('/{id}', 'PhoneController@destroy');
        });
    });

    /**
     * ADMIN PANEL
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => [
            'checkUser',
            'checkAdmin'
        ]
    ], function ($router) {

    });
});
