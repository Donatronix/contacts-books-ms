<?php

return [

    'migrations' => 'migrations',
    'default' =>env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'neo4j' =>
            [
                'driver' => 'neo4j',
                'host'   => env('NEO_HOST', '0.0.0.0'),
                'port'   => env('NEO_PORT', '7474'),
                /*'database'   => env('NEO_DATABASE', 'stats'),*/
                'username' => env('NEO_USERNAME', null),
                'password' => env('NEO_PASSWORD', null),
                'ssl' => false
            ],

        'mysql' =>
            [
                'driver' => 'mysql',
                'host'   => env('DB_HOST', 'localhost'),
                'port'   => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'contacts'),
                'username' => env('DB_USERNAME', 'sumra'),
                'password' => env('DB_PASSWORD', '123Qwe456#@!'),
            ]
    ],
];
