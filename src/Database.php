<?php

class Database
{
    private PDO | null $connection;

    private $error;

    private PDOStatement $stmt;

    private $dbconnected = false;

    private $fetch_style = PDO::FETCH_ASSOC;

    public function __construct(
        private string $host,
        private string $dbname,
        private string $username,
        private string $password
    ) {
        $this->connect();
    }

    private function connect()
    {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8';
        $options = array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        );

        try {
            $this->connection = new \PDO($dsn, $this->username, $this->password, $options);
            $this->dbconnected = true;
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function isConnected(): bool
    {
        return $this->dbconnected;
    }

    public function query(string $query): void
    {
        $this->stmt = $this->connection->prepare($query);
    }

    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    public function exec(string $query): int | bool
    {
        return $this->connection->exec($query);
    }

    public function resultset(): array
    {
        $data = $this->stmt->fetchAll($this->fetch_style);
        if (is_array($data)) {
            return $data;
        }
    }

    public function rowCount(): int
    {
        $data = $this->stmt->rowCount();
        if (is_int($data)) {
            return $data;
        }
    }

    public function single(): array | bool
    {
        $data = $this->stmt->fetch($this->fetch_style);

        if (is_array($data)) {
            return $data;
        } else {
            return false;
        }
    }

    public function lastInsertId(): int | false
    {
        return $this->connection->lastInsertId();
    }

    public function bind(string $param, mixed $value, int $type = null): void
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function returnDbName(): string
    {
        return $this->dbname;
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}
