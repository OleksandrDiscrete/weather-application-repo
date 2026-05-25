<?php
namespace WeatherMaster\Repositories;

include_once __DIR__ . "/../data/database.php";

use PDO;
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

    public abstract function initTable(): void;
    /**
     * @param T $item
     */
    public abstract function add($item): bool;
    public abstract function remove(int $id): bool;
    public abstract function seed(): void;
}
