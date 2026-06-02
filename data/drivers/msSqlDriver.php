<?php
namespace WeatherMaster\Data\Drivers;

class MsSqlDriver
{
    /**
     * @var resource|false|null
     */
    private mixed $connection = null;

    public function connect(array $config)
    {
        if ($this->connection === null) {
            $this->connection = sqlsrv_connect($config['host'], [
                "Database" => $config['db'],
                "UID" => $config['user'],
                "PWD" => $config['pass']
            ]);
        }
    }

    public function disconnect()
    {
        if ($this->connection === null) {
            return;
        }

        sqlsrv_close($this->connection);
        $this->connection = null;
    }

    private function prepareQuery(string $sql, array $params = []): array
    {
        if (empty($params)) {
            return [$sql, []];
        }

        $orderedParams = [];
        $sql = preg_replace_callback(
            '/:([a-zA-Z0-9_]+)/',
            function ($matches) use ($params, &$orderedParams) {
                $orderedParams[] = $params[$matches[1]] ?? null;
                return '?';
            },
            $sql
        );

        return [$sql, $orderedParams];
    }

    private function execute(string $sql, array $params = [])
    {
        [$sql, $params] = $this->prepareQuery($sql, $params);
        return sqlsrv_query($this->connection, $sql, $params);
    }

    public function executeWithParameters(string $sql, array $params = []): mixed
    {
        return $this->execute($sql, $params);
    }

    public function fetchRow(string $sql, array $params = []): mixed
    {
        $stmt = $this->execute($sql, $params);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    public function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = $this->execute($sql, $params);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
        return $row[0] ?? null;
    }

    public function handleTransaction(callable $callback): mixed
    {
        if (!sqlsrv_begin_transaction($this->connection)) {
            die("Unable to start transaction: " . print_r(sqlsrv_errors(), true));
        }

        try {
            $result = $callback($this->connection);

            if (!sqlsrv_commit($this->connection)) {
                throw new \RuntimeException("Unable to commit transaction: " . print_r(sqlsrv_errors(), true));
            }

            return $result;
        } catch (\Throwable $e) {
            sqlsrv_rollback($this->connection);
            die("Transaction failed: " . $e->getMessage());
        }
    }
}