<?php
namespace WeatherMaster\Data\Adapters;

include_once __DIR__ . "/../database.php";
include_once __DIR__ . '/../drivers/msSqlDriver.php';

use WeatherMaster\Data\DatabaseInterface;
use WeatherMaster\Data\Drivers\MsSqlDriver;

class MsSqlDatabaseAdapter implements DatabaseInterface
{
    private MsSqlDriver $driver;
    public function __construct(private array $config)
    {
        $this->driver = new MsSqlDriver();
    }

    public function connect(): void
    {
        $this->driver->connect($this->config);
    }

    public function disconnect(): void
    {
        $this->driver->disconnect();
    }

    public function fetchOne(string $sql, array $params = []): mixed
    {
        return $this->driver->fetchRow($sql, $params);
    }
    public function fetchMany(string $sql, array $params = []): array
    {
        return $this->driver->fetchAll($sql, $params);
    }
    public function execute(string $query): mixed
    {
        return $this->driver->executeWithParameters($query, []);
    }

    public function executeWithParameters(string $query, array $params = []): mixed
    {
        return $this->driver->executeWithParameters($query, $params);
    }

    public function fetchColumn(string $query): mixed
    {
        return $this->driver->fetchColumn($query);
    }

    public function handleTransaction(callable $callback): mixed
    {
        return $this->driver->handleTransaction($callback);
    }
}