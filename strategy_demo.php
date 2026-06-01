<?php
namespace WeatherMaster;

include_once __DIR__ . "/helpers/pathHelper.php";
include_once __DIR__ . "/base.php";
include_once __DIR__ . "/strategies/TemperatureStrategyInterface.php";
include_once __DIR__ . "/strategies/CelsiusStrategy.php";
include_once __DIR__ . "/strategies/FahrenheitStrategy.php";
include_once __DIR__ . "/services/WeatherDisplayService.php";

use WeatherMaster\BasePage;
use WeatherMaster\Strategies\CelsiusStrategy;
use WeatherMaster\Strategies\FahrenheitStrategy;
use WeatherMaster\Services\WeatherDisplayService;

class StrategyDemoPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Паттерн Strategy");
    }

    public function get(): void
    {
        $baseTemp = 25.5; 
        $celsiusStrategy = new CelsiusStrategy();
        $fahrenheitStrategy = new FahrenheitStrategy();

        $weatherService = new WeatherDisplayService($celsiusStrategy);
        $celsiusResult = $weatherService->displayTemperature($baseTemp);

        $weatherService->setStrategy($fahrenheitStrategy);
        $fahrenheitResult = $weatherService->displayTemperature($baseTemp);

        $content = <<<HTML
        <section class="py-5">
            <div class="container">
                <h1 class="mb-4 text-center"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Шаблон проєктування Strategy</h1>
                
                <div class="card shadow mx-auto" style="max-width: 600px;">
                    <div class="card-body text-center">
                        <p class="text-muted mb-4">Ця сторінка демонструє застосування патерну Стратегія для конвертації температури.</p>
                        
                        <h4 class="mb-4">Базова температура в системі: <b>{$baseTemp}</b></h4>
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-5">Використано <code>CelsiusStrategy</code>:</span>
                            <span class="badge bg-success fs-5">{$celsiusResult}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5">Використано <code>FahrenheitStrategy</code>:</span>
                            <span class="badge bg-warning text-dark fs-5">{$fahrenheitResult}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
HTML;
        $this->printBasePage($content);
    }

    public function post(): void
    {
        $this->get();
    }
}

$page = new StrategyDemoPage();
$page->render();