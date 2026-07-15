<?php

return [
    'GET' => [
        '/'              => ['controller' => 'HomeController',  'method' => 'index'],
        '/pricing'       => ['controller' => 'HomeController',  'method' => 'pricing'],
        '/blog'          => ['controller' => 'BlogController',  'method' => 'index'],
        '/blog/{slug}'   => ['controller' => 'BlogController',  'method' => 'show'],
        '/internships'   => ['controller' => 'InternshipController', 'method' => 'index'],
        '/apply/{id}'    => ['controller' => 'ApplicationController', 'method' => 'apply'],
        '/login'         => ['controller' => 'AuthController',  'method' => 'showLogin'],
        '/dashboard'     => ['controller' => 'DashboardController', 'method' => 'index', 'auth' => true],
    ],
     'POST' => [
        '/apply/{id}'    => ['controller' => 'ApplicationController', 'method' => 'apply'],
    ],
];