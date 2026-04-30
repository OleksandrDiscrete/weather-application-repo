<?php

include_once "baseRepository.php";
include_once "../models/city.php";

use BaseRepository;
use City;

/**
 * @extends BaseRepository<City>
 */
class CityRepository extends BaseRepository
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function init_table(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS " . City::TABLE_NAME . " (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    position_x REAL NOT NULL,
                    position_y REAL NOT NULL,
                    added_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";

            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage();
        }
    }

    /**
     * @param City $item
     */
    public function add($item): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO " .
                City::TABLE_NAME .
                "(name, position_x, position_y) VALUES (:name, :position_x, :position_y)");

            $stmt->bindParam(':name', $item->name);
            $stmt->bindParam(':position_x', $item->position_x);
            $stmt->bindParam(':position_y', $item->position_y);

            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            die("Error inserting data: " . $e->getMessage());
        }
    }
    public function remove(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM " . City::TABLE_NAME . " WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error deleting data: " . $e->getMessage());
        }
    }
}