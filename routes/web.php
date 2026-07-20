<?php

return [
    'GET' => [
        '/'              => ['controller' => 'HomeController',       'method' => 'index'],
        '/pricing'       => ['controller' => 'HomeController',       'method' => 'pricing'],
        '/blog'          => ['controller' => 'BlogController',       'method' => 'index'],
        '/blog/{slug}'   => ['controller' => 'BlogController',       'method' => 'show'],
        '/internships'   => ['controller' => 'InternshipController', 'method' => 'index'],
        '/apply/{id}'    => ['controller' => 'ApplicationController','method' => 'apply'],
        '/login'         => ['controller' => 'AuthController',       'method' => 'showLogin'],
        '/dashboard'     => ['controller' => 'DashboardController',  'method' => 'index'], // pas de 'auth' => true ici !
        '/incidents' => ['controller' => 'IncidentController', 'method' => 'index'],
        '/soc-config' => ['controller' => 'SOCController', 'method' => 'index'],
        '/reports' => ['controller' => 'ReportController', 'method' => 'index'],
    ],
    'POST' => [
        '/apply/{id}'    => ['controller' => 'ApplicationController','method' => 'apply'],
    ],
];