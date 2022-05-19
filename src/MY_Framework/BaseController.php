<?php

namespace MY_Framework;

interface BaseController
{
    public function processRequest(string $method, ?string $id): void;

    public function responedUnprocessableEntity(array $errors);

    public function responedMethodNotAllowed(array $allowed_method): void;

    public function responedNotFound(string $id): void;

    public function responedCreated(string $id): void;

    public function getValidationErrors(array $data, bool $is_new = true): array;
}
