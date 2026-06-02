<?php
namespace WeatherMaster\Repositories;

include_once __DIR__ . "/../data/database.php";
use WeatherMaster\Data\DatabaseInterface;

/**
 * @template T
 */
abstract class BaseRepository
{
    protected DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }
    protected function initAndSeed(): void
    {
        $this->db->handleTransaction(function () {
            $this->initTable();
            $this->seed();
        });
    }
    public abstract function initTable(): void;
    /**
     * @param T $item
     */
    public abstract function add($item): bool;
    public abstract function remove(int $id): bool;
    public abstract function seed(): void;
}
