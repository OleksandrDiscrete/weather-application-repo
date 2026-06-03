<?php
namespace WeatherMaster\Repositories;

include_once "baseRepository.php";
include_once __DIR__ . "/../models/city.php";
include_once __DIR__ . "/../data/database.php";

use WeatherMaster\Models\City;
use WeatherMaster\Data\DatabaseInterface;

interface CityRepositoryInterface
{
    public function add(City $city): bool;
    public function getByName(string $name): ?City;
    public function getAll(): array;
}

/**
 * @extends BaseRepository<City>
 */
class CityRepository extends BaseRepository implements CityRepositoryInterface
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct($db);
        $this->initAndSeed();
    }

    public function initTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . City::TABLE_NAME . " (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                position_x REAL NOT NULL,
                position_y REAL NOT NULL,
                info_url TEXT NOT NULL DEFAULT '',
                added_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
        $this->db->execute($sql);
    }

    /**
     * @param City $item
     */
    public function add($item): bool
    {
        $sql = "INSERT INTO " .
            City::TABLE_NAME .
            "(name, position_x, position_y, info_url) VALUES (:name, :positionX, :positionY, :infoUrl)";

        return $this->db->executeWithParameters($sql, [
            'name' => $item->name,
            'positionX' => $item->positionX,
            'positionY' => $item->positionY,
            'infoUrl' => $item->infoUrl
        ]);
    }

    public function remove(int $id): bool
    {
        $sql = "DELETE FROM " . City::TABLE_NAME . " WHERE id = :id";
        return $this->db->executeWithParameters($sql, ['id' => $id]);
    }

    public function getAll(): array
    {
        $rows = $this->db->fetchMany("SELECT * FROM " . City::TABLE_NAME . " ORDER BY name ASC");

        $cities = [];
        foreach ($rows as $row) {
            $cities[] = new City(
                id: (int) $row['id'],
                name: $row['name'],
                positionX: (float) $row['position_x'],
                positionY: (float) $row['position_y'],
                infoUrl: $row['info_url']
            );
        }

        return $cities;
    }
    public function getByName(string $name): ?City
    {
        $data = $this->db->fetchOne("SELECT * FROM " . City::TABLE_NAME . " WHERE name = :name LIMIT 1", ['name' => $name]);

        if ($data) {
            $city = new City($data['id'], $data['name'], $data['position_x'], $data['position_y'], $data['info_url']);
            return $city;
        }
        return null;
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

        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM " . City::TABLE_NAME);
        if ($count == 0) {
            foreach ($apiCityMap as $cityName => $coordinates) {
                $coords = explode(",", $coordinates);

                if (count($coords) === 2) {
                    $city = new City();
                    $city->name = $cityName;
                    $city->positionX = (float) $coords[0];
                    $city->positionY = (float) $coords[1];
                    $city->infoUrl = "";
                    $this->add($city);
                }
            }
        }
    }
}