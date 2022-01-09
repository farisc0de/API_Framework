<?php

class UserController
{
    public function __construct(
        private UserGateway $gateway,
        private JWTCodec $codec,
        private RefreshTokenGateway $rtg
    ) {
    }

    public function register(string $method)
    {
        if ($method == "POST") {
            $data = (array)json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidationErrors($data, true);

            if (!empty($errors)) {
                $this->responedUnprocessableEntity($errors);
                return;
            }

            $api_key = $this->gateway->createUser($data);

            $this->responedCreated($api_key);
        } else {
            $this->responedMethodNotAllowed(["POST"]);
        }
    }

    public function login(string $method)
    {

        if ($method == "POST") {
            $data = (array) json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidationErrors($data);

            if (!empty($errors)) {
                $this->responedUnprocessableEntity($errors);
                return;
            }

            $user_data = $this->gateway->getByUsername($data['username']);

            if ($user_data == false) {
                http_response_code(401);
                echo json_encode(["message" => "Invalid authentication"]);
                exit;
            }

            if (!password_verify($data['password'], $user_data['password_hash'])) {
                http_response_code(401);
                echo json_encode(["message" => "Invalid authentication"]);
                exit;
            }

            $this->generateToken($user_data, 1);
        } else {
            $this->responedMethodNotAllowed(["POST"]);
        }
    }

    public function refresh(string $method)
    {
        if ($method !== "POST") {
            $this->responedMethodNotAllowed(["POST"]);
            exit;
        }

        $data = (array) json_decode(file_get_contents("php://input"), true);

        if (!array_key_exists("token", $data)) {

            http_response_code(400);
            echo json_encode(["message" => "missing token"]);
            exit;
        }

        if ($this->rtg->getByToken($data['token']) === false) {
            http_response_code(400);
            echo json_encode(["message" => "invalid token (not on whitelist)"]);
            exit;
        }

        try {
            $payload = $this->codec->decode($data["token"]);
        } catch (Exception) {

            http_response_code(400);
            echo json_encode(["message" => "invalid token"]);
            exit;
        }

        $user_id = $payload["sub"];

        $user = $this->gateway->getById($user_id);

        if ($user === false) {

            http_response_code(401);
            echo json_encode(["message" => "invalid authentication"]);
            exit;
        }

        $this->generateToken($user, 2, $data['token']);
    }

    public function logout(string $method)
    {
        if ($method !== "POST") {
            $this->responedMethodNotAllowed(["POST"]);
            exit;
        }

        $data = (array) json_decode(file_get_contents("php://input"), true);

        if (
            !array_key_exists("token", $data)
        ) {

            http_response_code(400);
            echo json_encode(["message" => "missing token"]);
            exit;
        }

        if ($this->rtg->getByToken($data['token']) === false) {
            http_response_code(400);
            echo json_encode(["message" => "invalid token (not on whitelist)"]);
            exit;
        }

        $codec = new JWTCodec($_ENV["SECRET_KEY"]);

        try {
            $codec->decode($data["token"]);
        } catch (Exception) {
            http_response_code(400);
            echo json_encode(["message" => "invalid token"]);
            exit;
        }

        $this->rtg->delete($data["token"]);
    }

    private function generateToken(array $user_data, int $operation, string | null $old_token = null)
    {
        $payload = [
            "sub" => $user_data['id'],
            "name" => $user_data['name'],
            "exp" => time() + 300
        ];

        $accesss_token = $this->codec->encode($payload);
        $refresh_token_expiry = time() + 432000;
        $refresh_token = $this->codec->encode([
            "sub" => $user_data['id'],
            "exp" => time() + 432000
        ]);

        if ($operation == 2) {
            $this->rtg->delete($old_token);
        }

        $this->rtg->create($refresh_token, $refresh_token_expiry);

        echo json_encode([
            "access_token" => $accesss_token,
            "refresh_token" => $refresh_token,
            "message" => "Successful Login, Welcome {$user_data['name']}"
        ]);
    }

    public function responedUnprocessableEntity(array $errors)
    {
        http_response_code(442);

        echo json_encode(["errors" => $errors]);
    }

    public function responedMethodNotAllowed(array $allowed_method): void
    {
        http_response_code(405);

        header("Allow: " . implode(", ", $allowed_method));
    }

    public function responedCreated(string $api_key): void
    {
        http_response_code(201);

        echo json_encode([
            "api_key" => $api_key,
            "message" => "Thank you for registering."
        ]);
    }

    public function getValidationErrors(array $data, bool $is_new = false): array
    {
        $errors = [];

        if ($is_new && empty($data['name'])) {
            $errors[] = "Name is required";
        }

        if (empty($data['username'])) {
            $errors[] = "Username is required";
        }

        if (empty($data['password'])) {
            $errors[] = "Password is required";
        }

        return $errors;
    }
}
