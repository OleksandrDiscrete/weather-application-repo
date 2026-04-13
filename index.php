<?php

include_once "base.php";
include "forecast.php";
include "api.php";

class IndexPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Weather Master Homepage");
    }

    private function get_intro_content(): string
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

    private function get_weather_content(): string
    {
        $target_city = isset($_COOKIE["city"]) ? trim($_COOKIE["city"]) : null;

        if ($target_city && in_array($target_city, Forecast::$cities)) {
            $apiCityName = Forecast::$apiCityMap[$target_city] ?? null;

            if ($apiCityName) {
                $apiClient = new WeatherApiClient("134335a027cf4d58a78231326261304"); 
                $weatherData = $apiClient->getCurrentWeather($apiCityName);

                if ($weatherData) {
                    $tempC = $weatherData['current']['temp_c'];
                    $tempF = $weatherData['current']['temp_f'];
                    $windKph = $weatherData['current']['wind_kph'];
                    $humidity = $weatherData['current']['humidity'];
                    $pressureMb = $weatherData['current']['pressure_mb'];
                    $conditionText = $weatherData['current']['condition']['text'];
                    $iconUrl = $weatherData['current']['condition']['icon'];

                    $date = date('d.m.Y');
                    $dayName = Forecast::$days[date('N') - 1];
                    $heading = "Сьогодні в місті {$target_city}";

                    $weather_data_html = <<<HTML
                        <ul class="weather__list">
                            <li class="weather__item">
                                <div class="weather__item-day border-bottom pb-2 mb-3">
                                    <h4>{$dayName}</h4>
                                    <span class="text-muted">{$date}</span>
                                </div>
                                <h3 class="weather__item-status mb-4">
                                    <img src="{$iconUrl}" alt="weather icon"> {$conditionText}
                                </h3>
                                <p class="weather__item-metric"><strong>Температура:</strong> {$tempC}°C, {$tempF}°F</p>
                                <p class="weather__item-metric"><strong>Вітер:</strong> {$windKph} км/год</p>
                                <p class="weather__item-metric"><strong>Вологість:</strong> {$humidity}%</p>
                                <p class="weather__item-metric"><strong>Тиск:</strong> {$pressureMb} mbars</p>
                            </li>
                        </ul>
                    HTML;
                } else {
                    $heading = "Помилка";
                    $weather_data_html = "<p class='text-center text-danger'>Не вдалося отримати дані для міста {$target_city}.</p>";
                }
            } else {
                $heading = "Помилка конфігурації";
                $weather_data_html = "<p class='text-center text-danger'>Координати для міста {$target_city} не знайдені у словнику.</p>";
            }
        } else {
            $heading = "Оберіть Ваше місто, щоб перевірити погоду";
            $weather_data_html = <<<HTML
                <p class="text-center text-muted mt-4 fs-5">
                    Дані про погоду відсутні. Введіть назву міста у поле вище та натисніть кнопку пошуку.
                </p>
            HTML;
        }

        $result = <<<HTML
        <section class="weather py-5" id="weather">
                <div class="container">
                    <div class="weather__wrap">
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
                        {$weather_data_html}
                    </div>
                </div>
            </section>
        HTML;

        return $result;
    }

    public function get(): void
    {
        $content = $this->get_intro_content() . $this->get_weather_content();
        $this->print_base_page($content);
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