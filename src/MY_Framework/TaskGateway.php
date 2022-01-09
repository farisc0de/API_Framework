<?php

namespace MY_Framework;

class TaskGateway
{
    public function __construct(private Database $db)
    {
    }

    public function getAllForUser(int $user_id): array
    {
        $sql = "SELECT * FROM task WHERE user_id = :user_id ORDER BY name";

        $this->db->query($sql);

        $this->db->bind(":user_id", $user_id, \PDO::PARAM_INT);

        $this->db->execute();

        $data = [];

        foreach ($this->db->resultset() as $row) {
            $row['is_completed'] = (bool) $row['is_completed'];

            $data[] = $row;
        }

        return $data;
    }

    public function getForUser(int $user_id, string $id): array | false
    {
        $this->db->query("SELECT * FROM task WHERE id = :id AND user_id = :user_id");

        $this->db->bind(":id", $id, \PDO::PARAM_INT);
        $this->db->bind(":user_id", $user_id, \PDO::PARAM_INT);

        $this->db->execute();

        $data = $this->db->single();

        if ($data !== false) {
            $data['is_completed'] = (bool) $data['is_completed'];
        }

        return $data;
    }

    public function createForUser(int $user_id, array $data): string
    {
        $sql = "INSERT INTO task(name, priority, is_completed, user_id) 
        VALUES
         (:name, :priority, :is_completed, :user_id)";

        $this->db->query($sql);

        $this->db->bind(":name", $data['name'], \PDO::PARAM_STR);

        if (empty($data['priority'])) {
            $this->db->bind(":priority", null, \PDO::PARAM_NULL);
        } else {
            $this->db->bind(":priority", $data['priority'], \PDO::PARAM_INT);
        }

        $this->db->bind(":is_completed", $data['is_completed'] ?? false, \PDO::PARAM_BOOL);

        $this->db->bind(":user_id", $user_id, \PDO::PARAM_INT);

        $this->db->execute();

        return $this->db->lastInsertId();
    }

    public function updateForUser(int $user_id, string $id, array $data): int
    {
        $fields = [];

        if (!empty($data['name'])) {
            $fields['name'] = [
                $data['name'],
                \PDO::PARAM_STR
            ];
        }

        if (array_key_exists("priority", $data)) {
            $fields['priority'] = [
                $data['priority'],
                $data['priority'] === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT
            ];
        }

        if (array_key_exists("is_completed", $data)) {
            $fields['is_completed'] = [
                $data['is_completed'],
                \PDO::PARAM_BOOL
            ];
        }

        if (empty($fields)) {
            return 0;
        } else {
            $sets = array_map(function ($value) {
                return "$value = :$value";
            }, array_keys($fields));

            $sql_fields = implode(", ", $sets);

            $sql = "UPDATE task SET {$sql_fields} WHERE id = :id AND user_id = :user_id";

            $this->db->query($sql);

            $this->db->bind(":id", $id, \PDO::PARAM_INT);
            $this->db->bind(":user_id", $user_id, \PDO::PARAM_INT);

            foreach ($fields as $name => $value) {
                $this->db->bind(":{$name}", $value[0], $value[1]);
            }

            $this->db->execute();

            return $this->db->rowCount();
        }
    }

    public function deleteForUser(int $user_id, string $id): int
    {
        $sql = "DELETE FROM task WHERE id = :id AND user_id = :user_id";

        $this->db->query($sql);

        $this->db->bind(":id", $id, \PDO::PARAM_INT);
        $this->db->bind(":user_id", $user_id, \PDO::PARAM_INT);

        $this->db->execute();

        return $this->db->rowCount();
    }
}
