<?php

include_once "../data/database.php";

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
