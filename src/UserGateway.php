<?php

class UserGateway
{
    public function __construct(private Database $db)
    {
    }

    public function createUser(array $user_date): string
    {
        $sql = "INSERT INTO user (name, username, password_hash, api_key)
            VALUES (:name, :username, :password_hash, :api_key)";

        $this->db->query($sql);

        $password_hash = password_hash($user_date['password'], PASSWORD_BCRYPT);
        $api_key = bin2hex(random_bytes(16));

        $this->db->bind(":name", $user_date['name'], PDO::PARAM_STR);
        $this->db->bind(":username", $user_date['username'], PDO::PARAM_STR);
        $this->db->bind(":password_hash", $password_hash, PDO::PARAM_STR);
        $this->db->bind(":api_key", $api_key, PDO::PARAM_STR);

        $this->db->execute();

        return $api_key;
    }

    public function getByApiKey(string $key): array | false
    {
        $this->db->query("SELECT * FROM user WHERE api_key = :api_key");

        $this->db->bind(":api_key", $key, PDO::PARAM_STR);

        $this->db->execute();

        return $this->db->single();
    }

    public function getByUsername(string $username)
    {
        $sql = "SELECT * FROM user WHERE username = :username";

        $this->db->query($sql);

        $this->db->bind(":username", $username, PDO::PARAM_STR);

        $this->db->execute();

        return $this->db->single();
    }

    public function getById(int $id): array | false
    {
        $sql = "SELECT * FROM user WHERE id = :id";

        $this->db->query($sql);

        $this->db->bind(":id", $id);

        $this->db->execute();

        return $this->db->single();
    }
}
