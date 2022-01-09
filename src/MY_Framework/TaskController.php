<?php

namespace MY_Framework;

class TaskController
{
    public function __construct(private TaskGateway $gateway, private int $user_id)
    {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id == null) {
            if ($method == "GET") {
                echo json_encode($this->gateway->getAllForUser($this->user_id));
            } elseif ($method == "POST") {
                $data = (array)json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    $this->responedUnprocessableEntity($errors);
                    return;
                }

                $id = $this->gateway->createForUser($this->user_id, $data);

                $this->responedCreated($id);
            } else {
                $this->responedMethodNotAllowed(["GET", "POST"]);
            }
        } else {
            $task = $this->gateway->getForUser($this->user_id, $id);

            if ($task === false) {
                $this->responedNotFound($id);
                return;
            }

            switch ($method) {
                case 'GET':
                    echo json_encode($task);
                    break;
                case 'PATCH':
                    $data = (array)json_decode(file_get_contents("php://input"), true);

                    $errors = $this->getValidationErrors($data, false);

                    if (!empty($errors)) {
                        $this->responedUnprocessableEntity($errors);
                        return;
                    }

                    $rows = $this->gateway->updateForUser($this->user_id, $id, $data);

                    echo json_encode(["message" => "Record updated", "rows" => $rows]);

                    break;
                case "DELETE":
                    $rows = $this->gateway->deleteForUser($this->user_id, $id);
                    echo json_encode(["message" => "Record deleted", "rows" => $rows]);
                    break;

                default:
                    $this->responedMethodNotAllowed(["GET", "PATCH", "DELETE"]);
                    break;
            }
        }
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

    public function responedNotFound(string $id): void
    {
        http_response_code(404);

        echo json_encode([
            "id" => $id,
            "message" => "Record not found"
        ]);
    }

    public function responedCreated(string $id): void
    {
        http_response_code(201);

        echo json_encode([
            "id" => $id,
            "message" => "Record created"
        ]);
    }

    public function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data['name'])) {
            $errors[] = "name is required";
        }

        if (!(empty($data['priority']))) {
            if (filter_var($data['priority'], FILTER_VALIDATE_INT) === false) {
                $errors[] = "priority must be an integer";
            }
        }

        return $errors;
    }
}
