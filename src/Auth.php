<?php

class Auth
{

    private int $user_id;

    public function __construct(private UserGateway $user_gateway, private JWTCodec $codec)
    {
    }

    public function authenticateApiKey(): bool
    {
        if (empty($_SERVER['HTTP_X_API_KEY'])) {
            http_response_code(400);
            echo json_encode(["message" => "Missing API key"]);
            return false;
        }

        $api_key = $_SERVER['HTTP_X_API_KEY'];

        $user = $this->user_gateway->getByApiKey($api_key);

        if ($user == false) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid API key"]);
            return false;
        }

        $this->user_id = $user['id'];

        return true;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function authenticateAccessToken(): bool
    {
        if (empty($_SERVER["HTTP_AUTHORIZATION"])) {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        if (!preg_match("/^token\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches)) {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        try {
            $data = $this->codec->decode($matches[1]);
        } catch (InvalidSignatureException) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid signature"]);
            return false;
        } catch (TokenExpiredException) {
            http_response_code(401);
            echo json_encode(["message" => "Token has expired"]);
            return false;
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => $e->getMessage()]);
            return false;
        }

        $this->user_id = $data["sub"];

        return true;
    }

    public function authenticate($auth_by)
    {
        if ($auth_by == "token") {
            if (!$this->authenticateAccessToken()) {
                exit;
            }
        } elseif ($auth_by == "key") {
            if (!$this->authenticateApiKey()) {
                exit;
            }
        }
    }
}
