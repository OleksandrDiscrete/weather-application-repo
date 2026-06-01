<?php
namespace WeatherMaster\Services;

use WeatherMaster\Strategies\TemperatureStrategyInterface;

class WeatherDisplayService
{
    private TemperatureStrategyInterface $strategy;

    public function __construct(TemperatureStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(TemperatureStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }


    public function displayTemperature(float $celsius): string
    {
        return $this->strategy->format($celsius);
    }
}