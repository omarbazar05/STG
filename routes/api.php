<?php

return [
    'POST' => [
        '/api/login'        => ['controller' => 'AuthController', 'method' => 'login'],
        '/api/verify-otp'   => ['controller' => 'AuthController', 'method' => 'verifyOtp'],
        '/api/logout'       => ['controller' => 'AuthController', 'method' => 'logout',  'auth' => true],
        '/api/refresh'      => ['controller' => 'AuthController', 'method' => 'refresh', 'auth' => true],
        '/api/quote'        => ['controller' => 'QuoteController', 'method' => 'store'],
    ],
    'GET' => [
        '/api/notifications'        => ['controller' => 'NotificationController', 'method' => 'index',      'auth' => true],
        '/api/notifications/unread' => ['controller' => 'NotificationController', 'method' => 'unread',     'auth' => true],
         '/api/dashboard-data'       => ['controller' => 'DashboardController',    'method' => 'data',    'auth' => true],
    ],
    'PUT' => [
        '/api/notifications/{id}'   => ['controller' => 'NotificationController', 'method' => 'markRead',  'auth' => true],
        '/api/notifications/all'    => ['controller' => 'NotificationController', 'method' => 'markAllRead','auth' => true],
    ],
];