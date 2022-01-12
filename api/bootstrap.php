<?php

require dirname(__DIR__) . '/vendor/autoload.php';

header("Content-type: application/json; charset=UTF-8");

set_error_handler("MY_Framework\ErrorHandler::handleError");
set_exception_handler("MY_Framework\ErrorHandler::handleException");

use MY_Framework\JWTCodec;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));

$dotenv->load();

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$route = $parts[2];

$id = $parts[3] ?? null;

$method = $_SERVER['REQUEST_METHOD'];

$secret_key = $_ENV['SECRET_KEY'];

$codec = new JWTCodec($secret_key);
