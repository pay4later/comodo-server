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
            'factory'   => ['@hydrator', 'hydrate'],
            'arguments' => ['@comodo_settings', '!Pay4Later\Comodo\ComodoSettings']
        ]
    ]
];