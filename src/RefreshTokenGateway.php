<?php

class RefreshTokenGateway
{
    public function __construct(private Database $db, private string $key)
    {
    }

    public function create(string $token, int $expiry)
    {
        $hash = hash_hmac("sha256", $token, $this->key);

        $sql = "INSERT INTO refresh_token (token_hash, expires_at)
                VALUES (:token_hash, :expires_at)";

        $this->db->query($sql);

        $this->db->bind(":token_hash", $hash, PDO::PARAM_STR);
        $this->db->bind(":expires_at", $expiry, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function delete(string $token): int
    {
        $token_hash = hash_hmac("sha256", $token, $this->key);

        $sql = "DELETE FROM refresh_token WHERE token_hash = :token_hash";

        $this->db->query($sql);

        $this->db->bind(":token_hash", $token_hash, PDO::PARAM_STR);

        $this->db->execute();

        return $this->db->rowCount();
    }

    public function getByToken(string $token): array | false
    {
        $token_hash = hash_hmac("sha256", $token, $this->key);

        $sql = "SELECT * FROM refresh_token WHERE token_hash = :token_hash";

        $this->db->query($sql);

        $this->db->bind(":token_hash", $token_hash, PDO::PARAM_STR);

        $this->db->execute();

        return $this->db->single();
    }

    public function deleteExpired(): int
    {
        $sql = "DELETE FROM refresh_token
                WHERE expires_at < UNIX_TIMESTAMP()";

        $this->db->query($sql);

        $this->db->execute();

        return $this->db->rowCount();
    }
}
