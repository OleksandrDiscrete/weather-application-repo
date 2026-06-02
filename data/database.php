<?php
namespace WeatherMaster\Data;

use PDO;
use PDOException;

interface DatabaseInterface
{
    /**
     * Establishes and returns the database connection.
     */
    public function connect(): void;
    /**
     * Disconnects from the database.
     */
    public function disconnect(): void;
    public function execute(string $query): mixed;
    public function executeWithParameters(string $query, array $params = []): mixed;
    public function fetchOne(string $query, array $params = []): mixed;
    public function fetchMany(string $query, array $params = []): array;
    public function fetchColumn(string $query): mixed;
    public function handleTransaction(callable $callback): mixed;
}

class Database implements DatabaseInterface
{
    private ?PDO $pdo = null;

    public function __construct(private string $db_file_path = __DIR__ . '/main.db')
    {
    }
    public function __destruct()
    {
        $this->disconnect();
    }
    public function connect(): void
    {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO("sqlite:" . $this->db_file_path);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database Connection Failed: " . $e->getMessage());
            }
        }
    }
    public function disconnect(): void
    {
        $this->pdo = null;
    }
    public function execute(string $query): mixed
    {
        try {
            return $this->pdo->exec($query);
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function executeWithParameters(string $query, array $params = []): mixed
    {
        try {
            $stmt = $this->pdo->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function fetchOne(string $query, array $params = []): mixed
    {
        try {
            $stmt = $this->pdo->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error fetching data: " . $e->getMessage());
        }
    }
    public function fetchMany(string $query, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error fetching data: " . $e->getMessage());
        }
    }
    public function fetchColumn(string $query): mixed
    {
        try {
            $stmt = $this->pdo->query($query);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            die("Error fetching data: " . $e->getMessage());
        }
    }
    public function handleTransaction(callable $callback): mixed
    {
        try {
            $this->connect();
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
            }

            $result = $callback($this->pdo);

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }

            return $result;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $code = $this->pdo->errorCode();
            $info = $this->pdo->errorInfo();
            $msg = $info[2] ?? $e->getMessage();

            die("<h2 style='color:red;'>Помилка бази даних! Неможливо створити або заповнити таблиці.</h2>
                 <p><strong>Код помилки PDO:</strong> {$code}</p>
                 <p><strong>Деталі (errorInfo):</strong> {$msg}</p>");
        }
    }
}
