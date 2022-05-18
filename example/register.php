<?php

include_once  "../vendor/autoload.php";
include_once '../src/MY_Framework/Database.php';
include_once '../src/MY_Framework/UserGateway.php';

use MY_Framework\Database;
use MY_Framework\UserGateway;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $dotenv = Dotenv\Dotenv::createImmutable('../');
    $dotenv->load();

    $database = new Database(
        $_ENV["DB_HOST"],
        $_ENV["DB_NAME"],
        $_ENV["DB_USER"],
        $_ENV["DB_PASS"]
    );

    $user = new UserGateway($database);

    $api_key = $user->createUser(
        $_POST
    );

    echo "Thank you for registering. Your API key is ", $api_key;

    exit;
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
</head>

<body>

    <main class="container">

        <h1>Register</h1>

        <form method="post">

            <label for="name">
                Name
                <input name="name" id="name">
            </label>

            <label for="username">
                Username
                <input name="username" id="username">
            </label>

            <label for="password">
                Password
                <input type="password" name="password" id="password">
            </label>

            <button>Register</button>
        </form>

    </main>

</body>

</html>