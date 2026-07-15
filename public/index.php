<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers/env.php';

use App\Middlewares\AuthMiddleware;
use App\Middlewares\CSRFMiddleware;

// 1. Charger dotenv EN PREMIER
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 2. Charger les helpers et config
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/helpers/validator.php';
require_once __DIR__ . '/../app/helpers/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

// 3. Démarrer la session
session_start();

// 4. Charger les routes
$webRoutes = require __DIR__ . '/../routes/web.php';
$apiRoutes = require __DIR__ . '/../routes/api.php';

// 5. Récupérer la méthode HTTP et l'URI
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 6. Fusionner les routes web et api
$allRoutes = array_merge_recursive($webRoutes, $apiRoutes);
$routes    = $allRoutes[$method] ?? [];

// 7. Trouver la route correspondante (avec support des paramètres {id}, {slug}...)
$matchedRoute  = null;
$routeParams   = [];

foreach ($routes as $pattern => $route) {
    // Convertir le pattern en regex : /blog/{slug} → /blog/([^/]+)
    $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';

    if (preg_match($regex, $uri, $matches)) {
        array_shift($matches); // enlève le match complet
        $matchedRoute = $route;
        $routeParams  = $matches;
        break;
    }
}

// 8. Route non trouvée
if (!$matchedRoute) {
    http_response_code(404);
    echo json_encode(['error' => 'Route non trouvée']);
    exit;
}

// 9. Appliquer les middlewares si route protégée
if (!empty($matchedRoute['auth'])) {
    require_once __DIR__ . '/../app/middlewares/RateLimitMiddleware.php';
    require_once __DIR__ . '/../app/middlewares/CSRFMiddleware.php';
    require_once __DIR__ . '/../app/middlewares/AuthMiddleware.php';

    \App\Middlewares\CSRFMiddleware::handle();
    $user = \App\Middlewares\AuthMiddleware::handle();

    if ($user === false) exit;
}

// 10. Charger et appeler le controller
$controllerName = $matchedRoute['controller'];
$methodName     = $matchedRoute['method'];
$controllerFile = __DIR__ . "/../app/controllers/{$controllerName}.php";

if (!file_exists($controllerFile)) {
    http_response_code(500);
    echo json_encode(['error' => "Controller {$controllerName} introuvable"]);
    exit;
}

require_once $controllerFile;

$controllerClass = "App\\Controllers\\{$controllerName}";
$controller = new $controllerClass();
$controller->$methodName(...$routeParams);