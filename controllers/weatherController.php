<?php
namespace WeatherMaster\Controllers;

include_once __DIR__ . "/../data/database.php";
include_once __DIR__ . "/../repositories/cityRepository.php";
include_once __DIR__ . "/../services/regexService.php";
include_once __DIR__ . "/../services/api.php";
include_once __DIR__ . "/../helpers/pathHelper.php";
include_once __DIR__ . "/../helpers/forecastHelper.php";

use WeatherMaster\Data\Database;
use WeatherMaster\Helpers\ForecastHelper;
use WeatherMaster\Repositories\CityRepository;
use WeatherMaster\Services\RegexService;
use WeatherMaster\Services\WeatherApiClient;
use WeatherMaster\Helpers\PathHelper;

class WeatherController extends BaseController
{
    private CityRepository $cityRepository;

    public function __construct()
    {
        $db = new Database();
        $this->cityRepository = new CityRepository($db);
    }
    private function getDistanceToKyiv(array $coordinates): string
    {
        $kyivCoords = ForecastHelper::$KYIV_COORDINATES['lat'] . ", " . ForecastHelper::$KYIV_COORDINATES['lon'];
        $targetCoords = "{$coordinates['lat']}, {$coordinates['lon']}";
        $distanceFromCapital = RegexService::calculateDistance($kyivCoords, $targetCoords);

        return $distanceFromCapital ? "📍 Відстань від Києва: {$distanceFromCapital} км" : "";
    }
    public function index(): void
    {
        $targetCityName = isset($_COOKIE["city"]) ? trim($_COOKIE["city"]) : null;
        $heading = "Оберіть Ваше місто, щоб перевірити погоду";
        $alertMessage = "Дані про погоду відсутні. Введіть назву міста у поле вище та натисніть кнопку пошуку.";

        $statusClass = null;
        $distanceInfo = null;
        $weatherData = null;
        $targetCity = $this->cityRepository->getByName($targetCityName);
        if (!$targetCity) {
            $alertMessage = "Дані про погоду у місті {$targetCityName} відсутні, оберіть найближчий пункт до вашого місця перебування";
        } else {
            $apiClient = new WeatherApiClient();
            $weatherData = $apiClient->getCurrentWeather("$targetCity->positionX,$targetCity->positionY");
            if ($weatherData) {
                $heading = "Сьогодні в місті {$targetCityName}";
                $statusClass = WeatherApiClient::getWeatherStatusClass($weatherData['current']['condition']['code']);
                $distanceInfo = $this->getDistanceToKyiv($weatherData['location']);
            } else {
                $heading = "Помилка";
                $alertMessage = "Не вдалося отримати дані для міста {$targetCityName}.";
            }
        }

        $cities = $this->cityRepository->getAll();
        $applicationAlert = $this->getApplicationAlert(); 
        $day = (int) date('d');
        $month = (int) date('m');
        $year = (int) date('Y');
        $dayName = RegexService::getDayOfWeek($day, $month, $year);

        $this->render('home/index', [
            'pageTitle'        => 'WeatherMaster Home',
            'heading'          => $heading,
            'alertMessage'     => $alertMessage,
            'weatherData'      => $weatherData,
            'targetCity'       => $targetCity,
            'cities'           => $cities,
            'distanceInfo'     => $distanceInfo,
            'statusClass' => $statusClass,
            'dayName' => $dayName,
            'applicationAlert' => $applicationAlert,
            'imagePath'        => PathHelper::getAbsolutePath("assets/images/weather-icon.png")
        ]);
    }
    public function weeklyForecast(): void
    {
        $targetCityName = isset($_COOKIE["city"]) ? trim($_COOKIE["city"]) : null;
        $heading = "Оберіть Ваше місто, щоб перевірити погоду";
        $alertMessage = "Дані про погоду відсутні. Введіть назву міста у поле вище та натисніть кнопку пошуку.";

        // $statusClass = null;
        $distanceInfo = null;
        $weatherData = null;
        $targetCity = $this->cityRepository->getByName($targetCityName);
        if (!$targetCity) {
            $alertMessage = "Дані про погоду у місті {$targetCityName} відсутні, оберіть найближчий пункт до вашого місця перебування";
        } else {
            $apiClient = new WeatherApiClient();
            $weatherData = $forecastData = $apiClient->getForecast("$city->positionX,$city->positionY", 5);
            if ($weatherData) {
                $heading = "Сьогодні в місті {$targetCityName}";
                // $statusClass = WeatherApiClient::getWeatherStatusClass($weatherData['current']['condition']['code']);
                $distanceInfo = $this->getDistanceToKyiv($weatherData['location']);
            } else {
                $heading = "Помилка";
                $alertMessage = "Не вдалося отримати дані для міста {$targetCityName}.";
            }
        }

        $cities = $this->cityRepository->getAll();
        $applicationAlert = $this->getApplicationAlert(); 
        $day = (int) date('d');
        $month = (int) date('m');
        $year = (int) date('Y');
        $dayName = RegexService::getDayOfWeek($day, $month, $year);

        $this->render('home/weekly', [
            'pageTitle'        => 'WeatherMaster Home',
            'heading'          => $heading,
            'alertMessage'     => $alertMessage,
            'weatherData'      => $weatherData,
            'targetCity'       => $targetCity,
            'cities'           => $cities,
            'distanceInfo'     => $distanceInfo,
            'statusClass' => $statusClass,
            'dayName' => $dayName,
            'applicationAlert' => $applicationAlert,
            'imagePath'        => PathHelper::getAbsolutePath("assets/images/weather-icon.png")
        ]);
    }
}