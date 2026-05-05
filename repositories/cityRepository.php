<?php

include_once "baseRepository.php";
include_once "../models/city.php";

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
    /**
     * Seeds the database with default cities and their coordinates.
     */
    public function seed(): void
    {
        $apiCityMap = [
            "Київ" => "50.45,30.52",
            "Львів" => "49.84,24.03",
            "Одеса" => "46.48,30.73",
            "Харків" => "50.00,36.23",
            "Дніпро" => "48.46,35.04",
            "Запоріжжя" => "47.83,35.16",
            "Кривий Ріг" => "47.91,33.39",
            "Миколаїв" => "46.97,31.99",
            "Вінниця" => "49.23,28.46",
            "Херсон" => "46.63,32.61",
            "Полтава" => "49.58,34.55",
            "Чернігів" => "51.49,31.28",
            "Черкаси" => "49.44,32.05",
            "Суми" => "50.90,34.79",
            "Житомир" => "50.25,28.65",
            "Горлівка" => "48.33,38.05",
            "Маріуполь" => "47.09,37.54",
            "Луганськ" => "48.57,39.30",
            "Рівне" => "50.61,26.25",
            "Івано-Франківськ" => "48.92,24.71",
            "Тернопіль" => "49.55,25.59"
        ];

        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM " . City::TABLE_NAME);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $this->pdo->beginTransaction();

                foreach ($apiCityMap as $cityName => $coordinates) {
                    $coords = explode(",", $coordinates);

                    if (count($coords) === 2) {
                        $city = new City();
                        $city->name = $cityName;
                        $city->position_x = (float) $coords[0];
                        $city->position_y = (float) $coords[1];
                        $this->add($city);
                    }
                }

                $this->pdo->commit();
                echo "City seed successful: Default cities imported.\n";
            }
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            die("Error seeding cities: " . $e->getMessage());
        }
    }
}