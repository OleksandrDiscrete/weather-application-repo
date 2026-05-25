<?php
namespace WeatherMaster\Repositories;

include_once "baseRepository.php";
include_once __DIR__ . "/../models/city.php";
include_once __DIR__ . "/../data/database.php";

use PDO;
use PDOException;
use WeatherMaster\Models\City;
use WeatherMaster\Data\Database;

/**
 * @extends BaseRepository<City>
 */
class CityRepository extends BaseRepository
{
    public function __construct(Database $db)
    {
        parent::__construct($db);

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

    public function initTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . City::TABLE_NAME . " (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                position_x REAL NOT NULL,
                position_y REAL NOT NULL,
                added_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";

        $this->pdo->exec($sql);
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
            $stmt->bindParam(':position_x', $item->positionX);
            $stmt->bindParam(':position_y', $item->positionY);

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

    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM " . City::TABLE_NAME . " ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error fetching data: " . $e->getMessage());
        }
    }

    public function getByName(string $name): ?City
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM " . City::TABLE_NAME . " WHERE name = :name LIMIT 1");
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $city = new City($data['id'], $data['name'], $data['position_x'], $data['position_y']);
                return $city;
            }
            return null;
        } catch (PDOException $e) {
            die("Error fetching data: " . $e->getMessage());
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

        $stmt = $this->pdo->query("SELECT COUNT(*) FROM " . City::TABLE_NAME);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            foreach ($apiCityMap as $cityName => $coordinates) {
                $coords = explode(",", $coordinates);

                if (count($coords) === 2) {
                    $city = new City();
                    $city->name = $cityName;
                    $city->positionX = (float) $coords[0];
                    $city->positionY = (float) $coords[1];
                    $this->add($city);
                }
            }
        }
    }
}