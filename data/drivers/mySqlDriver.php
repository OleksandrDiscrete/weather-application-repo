<?php
namespace WeatherMaster\Data\Drivers;

class MySqlDriver
{
    private ?\mysqli $connection = null;

    public function openConnection(string $host, string $db, string $user, string $pass)
    {
        if ($this->connection === null) {
            $this->connection = new \mysqli($host, $user, $pass, $db);
            if ($this->connection->connect_error) {
                die("MySQL Connection failed: " . $this->connection->connect_error);
            }
        }
    }

    public function closeConnection()
    {
        if ($this->connection === null) {
            return;
        }

        $this->connection->close();
        $this->connection = null;
    }

    private function convertNamedParameters(string $query, array $params): array
    {
        if (empty($params)) {
            return [$query, []];
        }

        $ordered = [];
        $query = preg_replace_callback(
            '/:([a-zA-Z0-9_]+)/',
            function ($matches) use ($params, &$ordered) {
                $ordered[] = $params[$matches[1]] ?? null;
                return '?';
            },
            $query
        );

        return [$query, $ordered];
    }

    private function bindParams(\mysqli_stmt $stmt, array $params): void
    {
        if (empty($params)) {
            return;
        }

        $types = '';
        $values = [];

        foreach ($params as $param) {
            if (is_int($param) || is_bool($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }

            $values[] = $param;
        }

        $refs = [];
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }

        array_unshift($refs, $types);
        $stmt->bind_param(...$refs);
    }

    public function execute(string $query): mixed
    {
        return $this->connection->query($query);
    }

    private function prepareStatement(
        string $query,
        array $params
    ): \mysqli_stmt {
        $stmt = $this->connection->prepare($query);
        if ($stmt === false) {
            die("MySQL prepare failed: " . $this->connection->error);
        }

        $this->bindParams($stmt, $params);
        return $stmt;
    }

    public function executeWithParameters(string $query, array $params = []): mixed
    {
        [$query, $params] = $this->convertNamedParameters($query, $params);
        $stmt = $this->prepareStatement($query, $params);

        if (!$stmt->execute()) {
            die("MySQL execute failed: " . $stmt->error);
        }

        return true;
    }

    public function fetchRow(string $query, array $params = []): mixed
    {
        if (empty($params)) {
            $result = $this->execute($query);
            if ($result === false) {
                die("MySQL query failed: " . $this->connection->error);
            }
            return $result->fetch_assoc() ?: null;
        }

        [$query, $params] = $this->convertNamedParameters($query, $params);
        $stmt = $this->prepareStatement($query, $params);

        if (!$stmt->execute()) {
            die("MySQL execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function fetchAll(string $query, array $params = []): array
    {
        if (empty($params)) {
            $result = $this->execute($query);
            if ($result === false) {
                die("MySQL query failed: " . $this->connection->error);
            }
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        [$query, $params] = $this->convertNamedParameters($query, $params);
        $stmt = $this->prepareStatement($query, $params);

        if (!$stmt->execute()) {
            die("MySQL execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchColumn(string $query): mixed
    {
        $result = $this->execute($query);
        if ($result === false) {
            die("MySQL query failed: " . $this->connection->error);
        }
        $row = $result->fetch_row();
        return $row[0] ?? null;
    }

    public function handleTransaction(callable $callback): mixed
    {
        if ($this->connection === null) {
            die("MySQL connection is not open.");
        }

        $this->connection->begin_transaction();

        try {
            $result = $callback($this->connection);
            $this->connection->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->connection->rollback();
            die("Transaction failed: " . $e->getMessage());
        }
    }
}