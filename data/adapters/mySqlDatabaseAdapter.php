<?php
namespace WeatherMaster\Data\Adapters;

include_once __DIR__ . "/../database.php";
include_once __DIR__ . '/../drivers/mySqlDriver.php';

use WeatherMaster\Data\DatabaseInterface;
use WeatherMaster\Data\Drivers\MySqlDriver;

class MySqlDatabaseAdapter implements DatabaseInterface
{
    private MySqlDriver $driver;

    public function __construct(
        private string $host = "localhost",
        private string $db = "weather_db",
        private string $user = "root",
        private string $password = "root"
    ) {
        $this->driver = new MySqlDriver();
    }

    public function connect(): void
    {
        $this->driver->openConnection($this->host, $this->db, $this->user, $this->password);
    }

    public function disconnect(): void
    {
        $this->driver->closeConnection();
    }

    public function fetchOne(string $query, array $params = []): mixed
    {
        return $this->driver->fetchRow($query, $params);
    }

    public function fetchMany(string $query, array $params = []): array
    {
        return $this->driver->fetchAll($query, $params);
    }

    public function fetchColumn(string $query): mixed
    {
        return $this->driver->fetchColumn($query);
    }

    public function execute(string $query): mixed
    {
        return $this->driver->execute($query);
    }

    public function executeWithParameters(string $query, array $params = []): mixed
    {
        return $this->driver->executeWithParameters($query, $params);
    }

    public function handleTransaction(callable $callback): mixed
    {
        return $this->driver->handleTransaction($callback);
    }
}