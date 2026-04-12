<?php

include_once "base.php";
include_once "forecast.php";

use BasePage;
use Forecast;

class WeeklyForecastPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Weather Master Weekly Forecast");
    }
    private function get_intro_content(): string
    {
        return <<<'HTML'
            <section class="intro py-5">
                <div class="container">
                    <div class="intro__wrap">
                        <div class="text-center">
                            <h1>Щотижневий прогноз</h1>
                            <p class="lead">Перевірте погоду в будь-якому місті України на тиждень!</p>
                            <a class="btn btn-primary mt-2" href="#weather">Переглянути прогноз</a>
                        </div>
                    </div>
                </div>
            </section>
        HTML;
    }

    private function get_weather_content(): string
    {
        $target_city = $_COOKIE["city"] ?? null;

        if (isset($target_city) && in_array($target_city, Forecast::$cities)) {
            $heading = "Прогноз на тиждень у місті {$target_city}";

            $weather_data_html = '<ul class="weather__list d-flex flex-wrap gap-3">';
            for ($i = 0; $i < count(Forecast::$days); $i++) {
                $dayName = Forecast::$days[$i];
                $date = date('d.m.Y', strtotime("+$i days"));

                $icon = Forecast::$weather_status_icons[$i % 3];
                $tempC = rand(15, 25);
                $tempF = round($tempC * 9 / 5 + 32, 1);
                $tempK = $tempC + 273.15;
                $windKmh = rand(10, 25);
                $windMs = round($windKmh / 3.6, 1);

                $weather_data_html .= <<<HTML
                    <li class="weather__item weather__item--mini">
                        <div class="weather__item-day border-bottom pb-2 mb-3">
                            <h4 class="mb-1">{$dayName}</h4>
                            <span class="text-muted">{$date}</span>
                        </div>
                        {$icon}
                        <p class="weather__item-metric"><strong>Температура:</strong> {$tempC}°C, {$tempF}°F, {$tempK}K</p>
                        <p class="weather__item-metric"><strong>Вітер:</strong> {$windKmh} км/год, {$windMs} м/с</p>
                        <p class="weather__item-metric"><strong>Вологість:</strong> 45%</p>
                        <p class="weather__item-metric"><strong>Індекс забруднення:</strong> 42 AQI (Добре)</p>
                        <p class="weather__item-metric"><strong>Тиск:</strong> 101325 Pa, 1.01 bars</p>
                    </li>
                HTML;
            }
            $weather_data_html .= '</ul>';

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
            setcookie("city", htmlspecialchars($_POST['city']), time() + Forecast::$COOKIE_LIFETIME, "/");
        }
        header("Location: ./weeklyForecast.php");
    }
}

$page = new WeeklyForecastPage();
$page->render();