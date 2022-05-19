<?php

declare(strict_types=1);

include_once __DIR__ . "/bootstrap.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

use MY_Framework\TaskController;
use MY_Framework\Auth;
use MY_Framework\Database;
use MY_Framework\TaskGateway;
use MY_Framework\UserGateway;
use MY_Framework\UserController;
use MY_Framework\RefreshTokenGateway;

$database = new Database(
    $_ENV['DB_HOST'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);

$user_gateway = new UserGateway($database);

$rtg = new RefreshTokenGateway($database, $secret_key);

$user = new UserController($user_gateway, $codec, $rtg);

$auth = new Auth($user_gateway, $codec);

$auth_needed_routes = ["tasks"];

if (in_array($route, $auth_needed_routes)) {
    $auth->authenticate($_ENV['AUTHENTICATE_BY']);

    $user_id = $auth->getUserId();
}

/** API Router */
switch ($route) {
    case 'tasks':
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
