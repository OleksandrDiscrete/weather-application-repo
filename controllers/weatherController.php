<?php
namespace WeatherMaster\Controllers;

use WeatherMaster\Data\Database;
use WeatherMaster\Helpers\ForecastHelper;
use WeatherMaster\Repositories\CityRepository;
use WeatherMaster\Services\RegexService;
use WeatherMaster\Services\WeatherApiClient;
use WeatherMaster\Data\Adapters\MySqlDatabaseAdapter;

class WeatherController extends BaseController
{
    private CityRepository $cityRepository;

    public function __construct()
    {
        parent::__construct();
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
    public function getIndex(): void
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

        $this->render('weather/index', [
            'pageTitle' => 'WeatherMaster Home',
            'heading' => $heading,
            'alertMessage' => $alertMessage,
            'weatherData' => $weatherData,
            'targetCity' => $targetCity,
            'cities' => $cities,
            'distanceInfo' => $distanceInfo,
            'statusClass' => $statusClass,
            'dayName' => $dayName,
            'applicationAlert' => $applicationAlert
        ]);
    }
    public function getWeeklyForecast(): void
    {
        $targetCityName = isset($_COOKIE["city"]) ? trim($_COOKIE["city"]) : null;
        $parameters = [
            'pageTitle' => 'WeatherMaster Weekly Forecast',
            'heading' => "Оберіть Ваше місто, щоб перевірити погоду",
            'alertMessage' => "Дані про погоду відсутні. Введіть назву міста у поле вище та натисніть кнопку пошуку.",
            'forecastData' => null,
            'targetCity' => null,
            'cities' => $this->cityRepository->getAll(),
            'applicationAlert' => $this->getApplicationAlert()
        ];

        if (!isset($targetCityName)) {
            $this->render('home/weekly', $parameters);
            return;
        }

        $targetCity = $this->cityRepository->getByName($targetCityName);
        if (!$targetCity) {
            $parameters['alertMessage'] = "Дані про погоду у місті {$targetCityName} відсутні, оберіть найближчий пункт до вашого місця перебування";
        } else {
            $apiClient = new WeatherApiClient();
            $forecastData = $apiClient->getForecast("$targetCity->positionX,$targetCity->positionY", 5);
            if ($forecastData) {
                $parameters["forecastData"] = $forecastData;
                $parameters["heading"] = "Сьогодні в місті {$targetCityName}";
            } else {
                $parameters["heading"] = "Помилка";
                $parameters['alertMessage'] =
                    "Не вдалося отримати дані для міста {$targetCityName}.";
            }
        }

        $this->render('weather/weekly', $parameters);
    }
    public function saveCity(): void
    {
        if (isset($_POST['city'])) {
            setcookie("city", htmlspecialchars(trim($_POST['city'])), time() + ForecastHelper::$COOKIE_LIFETIME, "/");
        }
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: " . $redirectUrl);
        exit();
    }

    public function getStrategyDemo(): void
    {
        $baseTemp = 25.5;
        $celsiusStrategy = new \WeatherMaster\Strategies\CelsiusStrategy();
        $fahrenheitStrategy = new \WeatherMaster\Strategies\FahrenheitStrategy();
        $weatherService = new \WeatherMaster\Services\WeatherDisplayService($celsiusStrategy);

        $celsiusResult = $weatherService->displayTemperature($baseTemp);

        $weatherService->setStrategy($fahrenheitStrategy);
        $fahrenheitResult = $weatherService->displayTemperature($baseTemp);

        $this->render('weather/strategy', [
            'pageTitle' => 'Паттерн Strategy',
            'baseTemp' => $baseTemp,
            'celsiusResult' => $celsiusResult,
            'fahrenheitResult' => $fahrenheitResult
        ]);
    }

    private string $xmlFilePath = __DIR__ . "/../data/guestbook.xml";
    private array $parsedEntries = [];
    private array $currentEntry = [];
    private string $currentData = '';

    public function getGuestbook(): void
    {
        $this->ensureXmlExists();

        $this->parsedEntries = [];
        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, "startTag", "endTag");
        xml_set_character_data_handler($parser, "contents");

        $xmlData = file_get_contents($this->xmlFilePath);
        xml_parse($parser, $xmlData, true);

        $message = '';
        if (isset($_GET['success']) && $_GET['success'] == '1') {
            $message = "Ваш відгук успішно додано за допомогою DOMDocument!";
        }
        $error = $_SESSION['guestbook_error'] ?? '';
        unset($_SESSION['guestbook_error']);

        $this->render('weather/guestbook', [
            'pageTitle' => 'Гостьова книга (XML)',
            'entries' => $this->parsedEntries,
            'message' => $message,
            'error' => $error
        ]);
    }
    public function postGuestbook(): void
    {
        $author = htmlspecialchars(trim($_POST['author'] ?? 'Анонім'));
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        $date = date('Y-m-d H:i');

        if (empty($message) || empty($author)) {
            $_SESSION['guestbook_error'] = "Усі поля повинні бути заповнені!";
            header("Location: /guestbook");
            exit();
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $this->ensureXmlExists();

        if (filesize($this->xmlFilePath) > 0) {
            $dom->load($this->xmlFilePath);
            $root = $dom->documentElement;
        } else {
            $root = $dom->createElement('guestbook');
            $dom->appendChild($root);
        }

        $entryNode = $dom->createElement('entry');
        $entryNode->appendChild($dom->createElement('author', $author));
        $entryNode->appendChild($dom->createElement('date', $date));
        $entryNode->appendChild($dom->createElement('message', $message));
        $root->appendChild($entryNode);

        if ($dom->save($this->xmlFilePath)) {
            header("Location: /guestbook?success=1");
        } else {
            $_SESSION['guestbook_error'] = "Помилка збереження XML файлу.";
            header("Location: /guestbook");
        }
        exit();
    }
    private function startTag($parser, $tagName, $attrs): void
    {
        if ($tagName === 'ENTRY') {
            $this->currentEntry = [];
        }
        $this->currentData = '';
    }
    private function contents($parser, $data): void
    {
        $this->currentData .= $data;
    }
    private function endTag($parser, $tagName): void
    {
        if (in_array($tagName, ['AUTHOR', 'DATE', 'MESSAGE'])) {
            $this->currentEntry[strtolower($tagName)] = htmlspecialchars(trim($this->currentData));
        } elseif ($tagName === 'ENTRY') {
            $this->parsedEntries[] = $this->currentEntry;
        }
    }

    private function ensureXmlExists(): void
    {
        if (!file_exists($this->xmlFilePath)) {
            $defaultXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<guestbook>\n</guestbook>";
            file_put_contents($this->xmlFilePath, $defaultXml);
        }
    }
    public function getAdapter(): void
    {
        $options = [
            'pageTitle' => 'Патерн Adapter',
            'cities' => [],
            'errorMessage' => ""
        ];

        try {
            $adapter = new MySqlDatabaseAdapter("127.0.0.1", "weather_master_db", "root", "");
            $adapter->connect();
            $options['cities'] = $adapter->fetchMany("SELECT * FROM cities");
            $adapter->disconnect();

        } catch (\Throwable $e) {
            $options['errorMessage'] = "Помилка БД: " . $e->getMessage();
        }

        $this->render('weather/adapter', $options);
    }
}