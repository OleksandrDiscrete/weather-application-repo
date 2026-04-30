<?php

include_once "../data/database.php";

use Database;

/**
 * @template T
 */
abstract class BaseRepository
{
    protected $db;
    protected $pdo;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->pdo = $this->db->connect();
    }

    public abstract function init_table(): void;
    /**
     * @param T $item
     */
    public abstract function add($item): bool;
    public abstract function remove(int $id): bool;
}
