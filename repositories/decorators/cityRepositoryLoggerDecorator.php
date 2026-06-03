<?php
namespace WeatherMaster\Repositories\Decorators;

use WeatherMaster\Models\City;
use WeatherMaster\Repositories\CityRepositoryInterface;

class CityRepositoryLoggerDecorator implements CityRepositoryInterface
{
    private CityRepositoryInterface $innerRepository;
    public const LOG_FILE_PATH = __DIR__ . "/../../data/db_activity.log";

    public function __construct(CityRepositoryInterface $innerRepository)
    {
        $this->innerRepository = $innerRepository;
    }

    public function add(City $city): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Preparing to insert Model(City). Name: {$city->name}, Coordinates: ({$city->positionX}, {$city->positionY})\n";
        file_put_contents(self::LOG_FILE_PATH, $logMessage, FILE_APPEND);
        return $this->innerRepository->add($city);
    }

    public function getByName(string $name): ?City
    {
        return $this->innerRepository->getByName($name);
    }

    public function getAll(): array
    {
        return $this->innerRepository->getAll();
    }
}