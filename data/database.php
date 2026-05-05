<?php

class Database
{
    private ?PDO $pdo = null;

    public function __construct(private string $db_file_path = __DIR__ . '/main.db') {}
    public function __destruct() 
    {
        $this->disconnect();
    }

    public function connect(): PDO
    {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO("sqlite:" . $this->db_file_path);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database Connection Failed: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }
    public function disconnect(): void
    {
        $this->pdo = null;
    }
}
