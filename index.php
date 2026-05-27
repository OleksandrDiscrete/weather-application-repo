<?php
namespace WeatherMaster;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "base.php";
include_once "services/api.php";
include_once "services/regexService.php";
include_once "helpers/forecastHelper.php";
include_once "helpers/pathHelper.php";
include_once "repositories/cityRepository.php";
include_once "models/city.php";
include_once "data/database.php";

use WeatherMaster\Data\Database;
use WeatherMaster\Helpers\ForecastHelper;
use WeatherMaster\Helpers\PathHelper;
use WeatherMaster\Models\City;
use WeatherMaster\Services\WeatherApiClient;
use WeatherMaster\Services\RegexService;
use WeatherMaster\Repositories\CityRepository;

class IndexPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Weather Master Homepage");
    }

    private function getIntroContent(): string
    {
        $alertHtml = $this->getAlert();
        return <<<HTML
            <section class="intro py-5">
                <div class="container">
                    <div class="intro__wrap">
                        <div class="text-center">
                            <h1>Weather Master</h1>
                            <p class="lead">Перевірте погоду в будь-якому місті України!</p>
                            <a class="btn btn-primary" href="#weather">Дізнатися погоду</a>
                        </div>
                        $alertHtml
                    </div>
                </div>
            </section>
        HTML;
    }
    private function getDistanceToKyivContent(array $coordinates): string
    {
        $kyivCoords = ForecastHelper::$KYIV_COORDINATES['lat'] . ", " . ForecastHelper::$KYIV_COORDINATES['lon'];
        $targetCoords = "{$coordinates['lat']}, {$coordinates['lon']}";
        $distanceFromCapital = RegexService::calculateDistance($kyivCoords, $targetCoords);

        return $distanceFromCapital ? "<p class='text-muted small'>📍 Відстань від Києва: {$distanceFromCapital} км</p>" : "";
    }
    private function getCityInfoSection(?City $city): string
    {
        if (!$city || empty($city->infoUrl)) {
            return "";
        }

        $cityName = htmlspecialchars($city->name);
        $infoUrl = htmlspecialchars($city->infoUrl);

        return <<<HTML
        <section class="city-info py-4 mb-5">
            <div class="container">
                <div class="card shadow-sm border-0 bg-white p-4 text-center mx-auto" style="max-width: 600px; border-radius: 20px;">
                    <h3 class="mb-3">Цікаво дізнатися більше про місто {$cityName}?</h3>
                    <p class="text-muted mb-4">
                        Перейдіть за посиланням, щоб переглянути офіційну інформацію, вебкамери або місцеві новини.
                    </p>
                    <div>
                        <a href="{$infoUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                            <i class="bi bi-globe me-2"></i> Більше про {$cityName}
                        </a>
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
        $day = (int) date('d');
        $month = (int) date('m');
        $year = (int) date('Y');
        $dayName = RegexService::getDayOfWeek($day, $month, $year);
        $distanceInfo = $this->getDistanceToKyivContent($weatherData['location']);

        return <<<HTML
            <li class="weather__item">
                <div class="weather__item-day border-bottom pb-2 mb-3">
                    <h4>{$dayName}</h4>
                    <span class="text-muted">{$date}</span>
                </div>
                <h3 class="weather__item-status mb-3 text-center {$statusClass}">
                    <img src="{$iconUrl}" alt="icon"> {$conditionText}
                </h3>
                <div class="weather__item-metrics">
                    <p class="weather__item-metric"><strong>Температура:</strong> {$tempC}°C, {$tempF}°F, {$tempK}K</p>
                    <p class="weather__item-metric"><strong>Відчувається як:</strong> {$feelsLikeC}°C</p>
                    <p class="weather__item-metric"><strong>Вітер:</strong> {$windKph} км/год</p>
                    <p class="weather__item-metric"><strong>Вологість:</strong> {$humidity}%</p>
                    <p class="weather__item-metric"><strong>Опади:</strong> {$precipMm} мм</p>
                    <p class="weather__item-metric"><strong>УФ-індекс:</strong> {$uvIndex}</p>
                    <p class="weather__item-metric"><strong>Видимість:</strong> {$visKm} км</p>
                    <p class="weather__item-metric"><strong>Тиск:</strong> {$pressureMb} mbars</p>
                </div>
                <div class="mt-4 pt-3 border-top text-center">
                    {$distanceInfo}
                </div>
            </li>
        HTML;
    }

    private function getWeatherContent(): string
    {
        $targetCityName = isset($_COOKIE["city"]) ? trim($_COOKIE["city"]) : null;
        $heading = "Оберіть Ваше місто, щоб перевірити погоду";
        $weatherDataHtml = <<<HTML
                <p class="text-center text-muted mt-4 fs-5">
                    Дані про погоду відсутні. Введіть назву міста у поле вище та натисніть кнопку пошуку.
                </p>
            HTML;

        if (!$targetCityName) {
            return $this->getWeatherSection($heading, $weatherDataHtml);
        }

        $database = new Database();
        $cityRepository = new CityRepository($database);
        $city = $cityRepository->getByName($targetCityName);

        if (!$city) {
            $weatherDataHtml = <<<HTML
                <p class="text-center text-danger mt-4 fs-5">
                    Дані про погоду у місті {$targetCityName} відсутні, оберіть найближчий пункт до вашого місця перебування
                </p>
            HTML;
            return $this->getWeatherSection($heading, $weatherDataHtml);
        }

        $apiClient = new WeatherApiClient();
        $weatherData = $apiClient->getCurrentWeather("$city->positionX,$city->positionY");
        if ($weatherData) {
            $heading = "Сьогодні в місті {$targetCityName}";
            $weatherCard = $this->getWeatherCardContent($weatherData, $apiClient->getWeatherStatusClass($weatherData['current']['condition']['code']));

            $weatherDataHtml = <<<HTML
                    <ul class="weather__list">
                        $weatherCard
                    </ul>
                HTML;
        } else {
            $heading = "Помилка";
            $weatherDataHtml = "<p class='alert alert-danger text-center'>Не вдалося отримати дані для міста {$targetCityName}.</p>";
        }

        $mainLayout = $this->getWeatherSection($heading, $weatherDataHtml);
        $cityInfoLayout = $this->getCityInfoSection($city);
        return $mainLayout . $cityInfoLayout;
    }

    private function getWeatherSection(string $heading, string $weatherDataHtml): string
    {
        $imagePath = PathHelper::getAbsolutePath("assets/images/search-icon.png");

        $database = new Database();
        $cityRepository = new CityRepository($database);
        $cities = $cityRepository->getAll();

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
        $result .= implode("", array_map(fn($value): string => "<option value=\"{$value}\" />", array_column($cities, 'name')));
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
            setcookie("city", htmlspecialchars(trim($_POST['city'])), time() + ForecastHelper::$COOKIE_LIFETIME, "/");
        }
        header("Location: ./index.php");
    }
}

$homepage = new IndexPage();
$homepage->render();