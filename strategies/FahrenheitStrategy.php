<?php
namespace WeatherMaster\Strategies;

class FahrenheitStrategy implements TemperatureStrategyInterface
{
    public function format(float $celsiusTemperature): string
    {
        $fahrenheit = ($celsiusTemperature * 9 / 5) + 32;
        return round($fahrenheit, 1) . " °F";
    }
}