<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => 'contacts',
    'namespace' => '\App\Api\V1\Controllers'
], function ($router) {
    /*
     * Main contacts routes
     * */
    $router->get('/', 'ContactController@index');
    $router->post('/import/vcard', 'ContactController@addvcard');
    $router->post('/import/google', 'ContactController@addgoogle');

    /**
     * ADMIN PANEL
     */
    $router->group(
        ['middleware' => 'checkUser'],
        function ($router) {
            /**
             * Management
             */
            $router->post('contacts', 'ContactController@store');
            $router->delete('contacts', 'ContactController@destroy');

            /**
             * ADMIN PANEL
             */
            $router->group([
                'prefix' => 'admin',
                'namespace' => 'Admin',
                'middleware' => 'checkAdmin'
            ], function ($router) {
                /**
                 * Refferals
                 */
                $router->get('referrals-list', 'ContactController@index');
                $router->get('referrals-list/{id:[\d]+}', 'ContactController@show');
            });
        }
    );
});
