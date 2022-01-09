<?php

declare(strict_types=1);

include_once __DIR__ . "/bootstrap.php";

$database = new Database(
    $_ENV['DB_HOST'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$route = $parts[2];

$id = $parts[3] ?? null;

$method = $_SERVER['REQUEST_METHOD'];

$secret_key = $_ENV['SECRET_KEY'];

$codec = new JWTCodec($secret_key);

$user_gateway = new UserGateway($database);

$rtg = new RefreshTokenGateway($database, $secret_key);

$user = new UserController($user_gateway, $codec, $rtg);

$auth = new Auth($user_gateway, $codec);

$method = $_SERVER['REQUEST_METHOD'];

/** API Router */
switch ($route) {
    case 'tasks':
        $auth->authenticate($_ENV['AUTHENTICATE_BY']);

        $user_id = $auth->getUserId();

        $task_gatway = new TaskGateway($database);

        $task_controller = new TaskController($task_gatway, $user_id);

        $task_controller->processRequest($method, $id);

        break;

    case 'register':
        $user->register($method);

        break;

    case 'login':
        $user->login($method);
        break;

    case 'logout':
        $user->logout($method);
        break;

    case 'refresh':
        $user->refresh($method);
        break;

    default:
        http_response_code(404);
        echo json_encode(["message" => "Page not found"]);
        break;
}