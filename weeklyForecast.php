<?php

include_once "base.php";
include_once "./services/api.php";
include_once "forecast.php";
include_once "pathHelper.php";

class WeeklyForecastPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Weather Master Weekly Forecast");
    }

    private function getIntroContent(): string
    {
        return <<<'HTML'
            <section class="intro py-5">
                <div class="container">
                    <div class="intro__wrap">
                        <div class="text-center">
                            <h1>Щотижневий прогноз</h1>
                            <p class="lead">Перевірте погоду в будь-якому місті України на найближчі дні!</p>
                            <a class="btn btn-primary mt-2" href="#weather">Переглянути прогноз</a>
                        </div>
                    </div>
                </div>
            </section>
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

        $heading = "Прогноз на найближчі дні у місті {$targetCity}";
        $apiCityName = Forecast::$apiCityMap[$targetCity] ?? null;
        if ($apiCityName) {
            $apiClient = new WeatherApiClient();

            $forecastData = $apiClient->getForecast($apiCityName, 5);
            if ($forecastData && isset($forecastData['forecast']['forecastday'])) {
                $weatherDataHtml = '<ul class="weather__list d-flex flex-wrap gap-3">';

                $i = 0;
                foreach ($forecastData['forecast']['forecastday'] as $dayData) {
                    $dateObj = strtotime($dayData['date']);
                    $date = date('d.m.Y', $dateObj);
                    $dayName = Forecast::$days[date('N', $dateObj) - 1];

                    if ($i === 0) {
                        $tempC = $forecastData['current']['temp_c'];
                        $tempF = $forecastData['current']['temp_f'];
                        $windKph = $forecastData['current']['wind_kph'];
                        $humidity = $forecastData['current']['humidity'];
                        $uvIndex = $forecastData['current']['uv'];
                        $precipMm = $forecastData['current']['precip_mm'];
                        $visKm = $forecastData['current']['vis_km'];
                        $conditionText = $forecastData['current']['condition']['text'];
                        $iconUrl = $forecastData['current']['condition']['icon'];
                        $statusClass = $apiClient->getWeatherStatusClass($forecastData['current']['condition']['code']);
                    } else {
                        $tempC = round($dayData['day']['avgtemp_c'], 1);
                        $tempF = round($dayData['day']['avgtemp_f'], 1);
                        $windKph = $dayData['day']['maxwind_kph'];
                        $humidity = $dayData['day']['avghumidity'];
                        $uvIndex = $dayData['day']['uv'];
                        $precipMm = $dayData['day']['totalprecip_mm'];
                        $visKm = $dayData['day']['avgvis_km'];
                        $conditionText = $dayData['day']['condition']['text'];
                        $iconUrl = $dayData['day']['condition']['icon'];
                        $statusClass = $apiClient->getWeatherStatusClass($dayData['day']['condition']['code']);
                    }

                    $tempK = round($tempC + 273.15, 1);
                    $windMs = round($windKph / 3.6, 1);

                    $maxTemp = $dayData['day']['maxtemp_c'];
                    $minTemp = $dayData['day']['mintemp_c'];
                    $chanceOfRain = $dayData['day']['daily_chance_of_rain'];

                    $weatherDataHtml .= <<<HTML
                            <li class="weather__item weather__item--mini" style="flex: 1 1 18.75rem;">
                                <div class="weather__item-day border-bottom pb-2 mb-3">
                                    <h4 class="mb-1">{$dayName}</h4>
                                    <span class="text-muted">{$date}</span>
                                </div>
                                <h5 class="weather__item-status mb-3 text-center {$statusClass}">
                                    <img src="{$iconUrl}" alt="icon"> {$conditionText}
                                </h5>
                                <p class="weather__item-metric"><strong>Температура:</strong> {$tempC}°C ({$minTemp}...{$maxTemp}), {$tempF}°F, {$tempK}K</p>
                                <p class="weather__item-metric"><strong>Вітер:</strong> {$windKph} км/год ({$windMs} м/с)</p>
                                <p class="weather__item-metric"><strong>Вологість:</strong> {$humidity}%</p>
                                <p class="weather__item-metric"><strong>Ймовірність дощу:</strong> {$chanceOfRain}%</p>
                                <p class="weather__item-metric"><strong>Опади:</strong> {$precipMm} мм</p>
                                <p class="weather__item-metric"><strong>УФ-індекс:</strong> {$uvIndex}</p>
                                <p class="weather__item-metric"><strong>Видимість:</strong> {$visKm} км</p>
                            </li>
                        HTML;
                    $i++;
                }
                $weatherDataHtml .= '</ul>';
            } else {
                $weatherDataHtml = "<p class='text-center text-danger'>Не вдалося завантажити прогноз.</p>";
            }
        } else {
            $heading = "Помилка конфігурації";
            $weatherDataHtml = "<p class='text-center text-danger'>Координати не знайдені.</p>";
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
                        <form class="weather__form" method="POST" action="./weeklyForecast.php" class="mt-4">
                            <div class="input-group mb-3">
                                <input type="text" list="cities" id="city" name="city" class="form-control" placeholder="Введіть назву міста..." required>
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
        header("Location: ./weeklyForecast.php");
    }
}

$page = new WeeklyForecastPage();
$page->render();