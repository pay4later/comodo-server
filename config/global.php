<?php

return [
    'comodo_settings' => [
        'path' => '/opt/COMODO'
    ],
    'factories' => [
        'controller' => 'Pay4Later\Controller\HomeController',
        'hydrator' => 'Pay4Later\Stdlib\HydratorService',
        'settings' => 'Pay4Later\Comodo\ComodoSettings',
        'Pay4Later\Comodo\ComodoSettings' => [
            'factory'   => [
                'object' => '@hydrator',
                'method' => 'hydrate',
                'params' => ['@comodo_settings', '!Pay4Later\Comodo\ComodoSettings']
            ],
        ],
        'guzzle' => 'Guzzle\Http\ClientInterface',
        'Guzzle\Http\ClientInterface' => [
            'object' => 'Guzzle\Http\Client',
            'methods' => [
                ['setUserAgent', ['Pay4Later Virus Scanner 1.0']]
            ]
        ],
    ]
];