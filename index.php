<?php

include_once "base.php";
include "forecast.php";

use BasePage;
use Forecast;

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
        $target_city = $_COOKIE["city"];

        if (isset($target_city) && in_array($target_city, Forecast::$cities)) {
            $date = date('d.m.Y');
            $dayName = Forecast::$days[date('N') - 1];

            $heading = "Сьогодні в місті {$target_city}";
            $icon = Forecast::$weather_status_icons[0];

            $weather_data_html = <<<HTML
                <ul class="weather__list">
                    <li class="weather__item">
                        <div class="weather__item-day border-bottom pb-2 mb-3">
                            <h4>{$dayName}</h4>
                            <span class="text-muted">{$date}</span>
                        </div>
                        {$icon}
                        <p class="weather__item-metric"><strong>Температура:</strong> 22°C, 71.6°F, 295.15K</p>
                        <p class="weather__item-metric"><strong>Вітер:</strong> 15 км/год, 4.1 м/с</p>
                        <p class="weather__item-metric"><strong>Вологість:</strong> 45%</p>
                        <p class="weather__item-metric"><strong>Індекс забруднення:</strong> 42 AQI (Добре)</p>
                        <p class="weather__item-metric"><strong>Тиск:</strong> 1013.25 hPa, 1010 mbars</p>
                    </li>
                </ul>
            HTML;
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
            setcookie("city", htmlspecialchars($_POST['city']), time() + Forecast::$COOKIE_LIFETIME, "/");
        }
        header("Location: ./index.php");
    }
}

$homepage = new IndexPage();
$homepage->render();