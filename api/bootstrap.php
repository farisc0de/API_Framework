<?php

require dirname(__DIR__) . '/vendor/autoload.php';

header("Content-type: application/json; charset=UTF-8");

set_error_handler("MY_Framework\ErrorHandler::handleError");
set_exception_handler("MY_Framework\ErrorHandler::handleException");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));

$dotenv->load();
