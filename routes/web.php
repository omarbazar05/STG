<?php

return [
    'GET' => [
        '/'              => ['controller' => 'HomeController',  'method' => 'index'],
        '/pricing'       => ['controller' => 'HomeController',  'method' => 'pricing'],
        '/blog'          => ['controller' => 'BlogController',  'method' => 'index'],
        '/blog/{slug}'   => ['controller' => 'BlogController',  'method' => 'show'],
        '/internships'   => ['controller' => 'StageController', 'method' => 'index'],
        '/apply/{id}'    => ['controller' => 'StageController', 'method' => 'apply'],
        '/login'         => ['controller' => 'AuthController',  'method' => 'showLogin'],
        '/dashboard'     => ['controller' => 'DashboardController', 'method' => 'index', 'auth' => true],
    ],
];