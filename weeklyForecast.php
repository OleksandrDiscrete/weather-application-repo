<?php

include_once "base.php";
include_once "forecast.php";
include_once "api.php"; 

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
                            <p class="lead">Перевірте погоду в будь-якому місті України на найближчі дні!</p>
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
            $heading = "Прогноз на найближчі дні у місті {$target_city}";

            $apiCityName = Forecast::$apiCityMap[$target_city] ?? $target_city;
            
            $apiClient = new WeatherApiClient("134335a027cf4d58a78231326261304"); 
            
            $forecastData = $apiClient->getForecast($apiCityName, 3);

            if ($forecastData && isset($forecastData['forecast']['forecastday'])) {
                $weather_data_html = '<ul class="weather__list d-flex flex-wrap gap-3">';
                
                foreach ($forecastData['forecast']['forecastday'] as $dayData) {
                    $dateObj = strtotime($dayData['date']);
                    $date = date('d.m.Y', $dateObj);
                    $dayName = Forecast::$days[date('N', $dateObj) - 1]; 

                    $tempC = round($dayData['day']['avgtemp_c'], 1);
                    $tempF = round($dayData['day']['avgtemp_f'], 1);
                    $tempK = round($tempC + 273.15, 1); 
                    $windKph = round($dayData['day']['maxwind_kph'], 1);
                    $windMs = round($windKph / 3.6, 1); 
                    $humidity = $dayData['day']['avghumidity'];
                    $conditionText = $dayData['day']['condition']['text'];
                    $iconUrl = $dayData['day']['condition']['icon'];

                    $weather_data_html .= <<<HTML
                        <li class="weather__item weather__item--mini">
                            <div class="weather__item-day border-bottom pb-2 mb-3">
                                <h4 class="mb-1">{$dayName}</h4>
                                <span class="text-muted">{$date}</span>
                            </div>
                            <h5 class="mb-3 text-center">
                                <img src="{$iconUrl}" alt="icon" style="width: 40px;"> {$conditionText}
                            </h5>
                            <p class="weather__item-metric"><strong>Температура:</strong> {$tempC}°C, {$tempF}°F, {$tempK}K</p>
                            <p class="weather__item-metric"><strong>Вітер:</strong> {$windKph} км/год, {$windMs} м/с</p>
                            <p class="weather__item-metric"><strong>Вологість:</strong> {$humidity}%</p>
                        </li>
                    HTML;
                }
                $weather_data_html .= '</ul>';
            } else {
                $weather_data_html = "<p class='text-center text-danger'>Не вдалося завантажити прогноз. Можливо, сервіс тимчасово недоступний.</p>";
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