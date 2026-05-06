<?php

include_once "base.php";
include_once "./services/api.php";
include_once "forecast.php";
include_once "pathHelper.php";

class IndexPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Weather Master Homepage");
    }

    private function getIntroContent(): string
    {
        return <<<'HTML'
            <section class="intro py-5">
                <div class="container">
                    <div class="intro__wrap">
                        <div class="text-center">
                            <h1>Weather Master</h1>
                            <p class="lead">Перевірте погоду в будь-якому місті України!</p>
                            <a class="btn btn-primary" href="#weather">Дізнатися погоду</a>
                        </div>
                    </div>
                </div>
            </section>
        HTML;
    }

    private function getWeatherCardContent(mixed $weatherData, string $weatherStatusClass): string
    {
        if (empty($weatherData))
            return "";

        $tempC = $weatherData['current']['temp_c'];
        $tempF = $weatherData['current']['temp_f'];
        $tempK = round($tempC + 273.15, 1);

        $feelsLikeC = $weatherData['current']['feelslike_c'];
        $uvIndex = $weatherData['current']['uv'];
        $precipMm = $weatherData['current']['precip_mm'];
        $visKm = $weatherData['current']['vis_km'];

        $windKph = $weatherData['current']['wind_kph'];
        $humidity = $weatherData['current']['humidity'];
        $pressureMb = $weatherData['current']['pressure_mb'];
        $conditionText = $weatherData['current']['condition']['text'];
        $iconUrl = $weatherData['current']['condition']['icon'];
        $statusClass = $weatherStatusClass;

        $date = date('d.m.Y');
        $dayName = Forecast::$days[date('N') - 1];

        return <<<HTML
            <li class="weather__item">
                <div class="weather__item-day border-bottom pb-2 mb-3">
                    <h4>{$dayName}</h4>
                    <span class="text-muted">{$date}</span>
                </div>
                <h3 class="weather__item-status mb-3 text-center {$statusClass}">
                    <img src="{$iconUrl}" alt="icon"> {$conditionText}
                </h3>
                <p class="weather__item-metric"><strong>Температура:</strong> {$tempC}°C, {$tempF}°F, {$tempK}K</p>
                <p class="weather__item-metric"><strong>Відчувається як:</strong> {$feelsLikeC}°C</p>
                <p class="weather__item-metric"><strong>Вітер:</strong> {$windKph} км/год</p>
                <p class="weather__item-metric"><strong>Вологість:</strong> {$humidity}%</p>
                <p class="weather__item-metric"><strong>Опади:</strong> {$precipMm} мм</p>
                <p class="weather__item-metric"><strong>УФ-індекс:</strong> {$uvIndex}</p>
                <p class="weather__item-metric"><strong>Видимість:</strong> {$visKm} км</p>
                <p class="weather__item-metric"><strong>Тиск:</strong> {$pressureMb} mbars</p>
            </li>
        HTML;
    }

    private function getWeatherContent(): string
    {
        $targetCity = isset($_COOKIE["city"]) ? trim($_COOKIE["city"]) : null;
        $heading = "Оберіть Ваше місто, щоб перевірити погоду";
        $weatherDataHtml = <<<HTML
                <p class="text-center text-muted mt-4 fs-5">
                    Дані про погоду відсутні. Введіть назву міста у поле вище та натисніть кнопку пошуку.
                </p>
            HTML;

        if (!$targetCity) {
            return $this->getWeatherSection($heading, $weatherDataHtml);
        }
        if (!in_array($targetCity, Forecast::$cities)) {
            $weatherDataHtml = <<<HTML
                <p class="text-center text-danger mt-4 fs-5">
                    Дані про погоду у місті {$targetCity} відсутні, оберіть найближчий пункт до вашого місця перебування
                </p>
            HTML;
            return $this->getWeatherSection($heading, $weatherDataHtml);
        }

        $apiCityName = Forecast::$apiCityMap[$targetCity] ?? null;
        if ($apiCityName) {
            $apiClient = new WeatherApiClient();

            $weatherData = $apiClient->getCurrentWeather($apiCityName);
            if ($weatherData) {
                $heading = "Сьогодні в місті {$targetCity}";
                $weatherCard = $this->getWeatherCardContent($weatherData, $apiClient->getWeatherStatusClass($weatherData['current']['condition']['code']));

                $weatherDataHtml = <<<HTML
                    <ul class="weather__list">
                        $weatherCard
                    </ul>
                HTML;
            } else {
                $heading = "Помилка";
                $weatherDataHtml = "<p class='alert alert-danger text-center'>Не вдалося отримати дані для міста {$targetCity}.</p>";
            }
        } else {
            $heading = "Помилка конфігурації";
            $weatherDataHtml = "<div class='alert alert-danger text-center'>Координати не знайдені.</div>";
        }
        return $this->getWeatherSection($heading, $weatherDataHtml);
    }

    private function getWeatherSection(string $heading, string $weatherDataHtml): string
    {
        $imagePath = PathHelper::getAbsolutePath("assets/images/search-icon.png");

        $result = <<<HTML
        <section class="weather py-5" id="weather">
                <div class="container">
                    <div class="weather__wrap">
                        <img src="$imagePath" alt="Magnifying glass icon" class="weather__image mb-4">
                        <div class="text-center mb-3">
                            <h2>{$heading}</h2>
                        </div>
                        <form class="weather__form" method="POST" action="./index.php" class="mt-4">
                            <div class="input-group mb-3">
                                <input type="text" list="cities" id="city" name="city" class="form-control" placeholder="Введіть назву міста..." aria-describedby="saveButton">
                                <button class="btn btn-secondary" type="submit" id="saveButton"><i class="bi bi-search"></i></button>
                            </div>
                            <datalist id="cities">
        HTML;
        $result .= implode("", array_map(fn($value): string => "<option value=\"{$value}\" />", Forecast::$cities));
        $result .= <<<HTML
                                </datalist>
                        </form>
                        {$weatherDataHtml}
                    </div>
                </div>
            </section>
        HTML;

        return $result;
    }

    public function get(): void
    {
        $content = $this->getIntroContent() . $this->getWeatherContent();
        $this->printBasePage($content);
    }

    public function post(): void
    {
        if (isset($_POST['city'])) {
            setcookie("city", htmlspecialchars(trim($_POST['city'])), time() + Forecast::$COOKIE_LIFETIME, "/");
        }
        header("Location: ./index.php");
    }
}

$homepage = new IndexPage();
$homepage->render();