<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => 'contacts',
    'namespace' => '\App\Api\V1\Controllers',
    'middleware' => 'checkUser'
], function ($router) {
    /**
     * Contacts
     */
    $router->get('/', 'ContactController@index');
    $router->post('/', 'ContactController@store');
    $router->get('/{id:[a-fA-F0-9\-]{36}}', 'ContactController@show');
    $router->put('/{id:[a-fA-F0-9\-]{36}}', 'ContactController@update');
    $router->delete('/{id:[a-fA-F0-9\-]{36}}', 'ContactController@destroy');
    $router->get('/{id:[a-fA-F0-9\-]{36}}/favorite', 'ContactController@favorite');
    $router->post('/merge', 'ContactController@merge');
    $router->post('/join-groups', 'ContactController@joinGroups');
    $router->post('/import/file', 'ContactController@importFile');
    $router->post('/import/json', 'ContactController@importJson');

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
        $router->post('/', 'ContactEmailController@store');
        $router->put('/{id}', 'ContactEmailController@update');
        $router->delete('/{id}', 'ContactEmailController@destroy');
    });

    /**
     * Contact Phones
     */
    $router->group([
        'prefix' => 'phones',
    ], function ($router) {
        $router->post('/', 'ContactPhoneController@store');
        $router->put('/{id}', 'ContactPhoneController@update');
        $router->delete('/{id}', 'ContactPhoneController@destroy');
    });

    /**
     * ADMIN PANEL
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => 'checkAdmin'
    ], function ($router) {
    });
});
