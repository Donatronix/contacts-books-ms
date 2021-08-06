<?php

return [
    /**
     * Pagination
     */
    'pagination_limit' => env('APP_PAGINATION_LIMIT', 10),

    /**
     * Microservices API
     */
    'api' => [
        'files' => [
            'host' => env('API_FILES_HOST', 'http://localhost:8080'),
            'version' => env('API_FILES_VERSION', '/v1'),
        ]
    ]
];


