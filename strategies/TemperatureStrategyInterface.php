<?php
namespace WeatherMaster\Strategies;

interface TemperatureStrategyInterface
{

    public function format(float $celsiusTemperature): string;
}