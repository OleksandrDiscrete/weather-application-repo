<?php
namespace WeatherMaster\Repositories;

include_once __DIR__ . "/../data/database.php";

use PDO;
use PDOException;
use WeatherMaster\Data\Database;

/**
 * @template T
 */
abstract class BaseRepository
{
    protected Database $db;
    protected ?PDO $pdo;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->connect();
    }
    protected function initAndSeed()
    {
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
            }

            $this->initTable();
            $this->seed();

            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
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
    public abstract function initTable(): void;
    /**
     * @param T $item
     */
    public abstract function add($item): bool;
    public abstract function remove(int $id): bool;
    public abstract function seed(): void;
}
