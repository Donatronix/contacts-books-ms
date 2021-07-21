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
    $router->get('/{id:[a-fA-F0-9\-]{36}}', 'ContactController@show');
    $router->post('/', 'ContactController@store');
    $router->delete('/{id:[a-fA-F0-9\-]{36}}', 'ContactController@destroy');
    $router->post('merge', 'ContactController@merge');
    $router->get('/{id:[a-fA-F0-9\-]{36}}/favorite', 'ContactController@favorite');

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
     * Contact External Imports
     */
    $router->group([
        'prefix' => 'import',
    ], function ($router) {
        $router->post('create', 'ImportController@create');
        $router->post('vcard', 'ImportController@addvcard');
        $router->post('google', 'ImportController@addgoogle');
    });

    /**
     *  For Remote
     */
    //$router->get('/remote', 'RemoteController@remote');

    /**
     *  For test
     */
    $router->get('run', '\App\Services\Import@run');
    $router->post('test', '\App\Services\Import@check');
    $router->get('run1', '\App\Services\Test@run');
    $router->get('test1', '\App\Services\Test@test');
    $router->get('run2', '\App\Services\CsvParser@run');
    $router->post('test2', '\App\Services\CsvParser@test');

    /**
     * ADMIN PANEL
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => 'checkAdmin'
    ], function ($router) {
        $router->get('list', 'ContactController@index');
        $router->get('list/{id:[\d]+}', 'ContactController@show');
    });
});
