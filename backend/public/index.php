<?php

declare(strict_types=1);

use App\Bootstrap\ApplicationFactory;
use App\Config\ApplicationConfig;
use App\Infrastructure\Database\ConnectionFactory;
use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, ApplicationConfig::allowedOrigins(), true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $graphQLController = ApplicationFactory::graphQLController(ConnectionFactory::create());
    $dispatcher = simpleDispatcher(
        static function (RouteCollector $routes) use ($graphQLController): void {
            $routes->post('/graphql', [$graphQLController, 'handle']);
            $routes->get('/health', static fn (): string => json_encode(['status' => 'ok']));
        }
    );
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $route = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $path);

    switch ($route[0]) {
        case Dispatcher::NOT_FOUND:
            http_response_code(404);
            echo json_encode(['errors' => [['message' => 'Not found.']]]);
            break;

        case Dispatcher::METHOD_NOT_ALLOWED:
            http_response_code(405);
            header('Allow: ' . implode(', ', $route[1]));
            echo json_encode(['errors' => [['message' => 'Method not allowed.']]]);
            break;

        case Dispatcher::FOUND:
            echo call_user_func($route[1]);
            break;
    }
} catch (Throwable $error) {
    http_response_code(500);
    error_log($error->getMessage());
    $message = ApplicationConfig::debugEnabled()
        ? $error->getMessage()
        : 'The service is temporarily unavailable.';
    echo json_encode(['errors' => [['message' => $message]]]);
}
