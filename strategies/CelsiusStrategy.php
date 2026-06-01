<?php
namespace WeatherMaster\Strategies;

class CelsiusStrategy implements TemperatureStrategyInterface
{
    public function format(float $celsiusTemperature): string
    {
        return round($celsiusTemperature, 1) . " °C";
    }
}