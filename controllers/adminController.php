<?php
namespace WeatherMaster\Controllers;

use WeatherMaster\Data\Database;
use WeatherMaster\Models\City;
use WeatherMaster\Repositories\CityRepositoryInterface;
use WeatherMaster\Repositories\CityRepository;
use WeatherMaster\Repositories\Decorators\CityRepositoryLoggerDecorator;
use WeatherMaster\Repositories\VisitRepository;
use WeatherMaster\Services\RegexService;

class AdminController extends BaseController
{
    private CityRepositoryInterface $cityRepository;
    private VisitRepository $visitRepository;

    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: /login");
            exit();
        }

        $db = new Database();
        $baseCityRepo = new CityRepository($db);
        $this->cityRepository = new CityRepositoryLoggerDecorator($baseCityRepo);
        $this->visitRepository = new VisitRepository($db);
    }

    /**
     * Display the main Admin Dashboard
     */
    public function getIndex(): void
    {
        $error = $_SESSION['admin_error'] ?? null;
        $message = $_SESSION['admin_message'] ?? null;
        unset($_SESSION['admin_error'], $_SESSION['admin_message']);

        $this->visitRepository->initTable();
        $statistics = [
            'total' => $this->visitRepository->getTotalCount(),
            'unique' => $this->visitRepository->getUniqueVisitorsCount(),
            'today' => $this->visitRepository->getTodayCount(),
            'pageStats' => $this->visitRepository->getPageStats(),
            'recent' => $this->visitRepository->getRecent(5)
        ];

        $alertFilePath = __DIR__ . "/../data/alert.txt";
        $currentAlertHtml = '';
        if (file_exists($alertFilePath)) {
            $rawText = file_get_contents($alertFilePath);
            $currentAlertHtml = RegexService::textToHtml($rawText);
            $currentAlertHtml = str_ireplace('<br>', "\n", $currentAlertHtml);
        }

        $this->render('admin/index', [
            'pageTitle' => 'Weather Master Admin',
            'adminName' => $_SESSION['admin_login'] ?? 'Admin',
            'error' => $error,
            'message' => $message,
            'statistics' => $statistics,
            'currentAlertHtml' => $currentAlertHtml
        ]);
    }

    /**
     * Handle POST to save the global alert
     */
    public function saveAlert(): void
    {
        $incomingHtmlFromAdmin = $_POST['alert_html'] ?? '';
        $cleanTextForFile = RegexService::htmlToText($incomingHtmlFromAdmin);
        $alertFilePath = __DIR__ . "/../data/alert.txt";

        if (file_put_contents($alertFilePath, $cleanTextForFile) !== false) {
            $_SESSION['admin_message'] = "Попередження успішно збережено!";
        } else {
            $_SESSION['admin_error'] = "Помилка збереження файлу. Перевірте права доступу до папки data/.";
        }

        header("Location: /admin");
        exit();
    }

    /**
     * Handle POST to add a new city
     */
    public function saveCity(): void
    {
        $infoUrl = trim($_POST['infoUrl'] ?? '');

        if (!empty($infoUrl) && !RegexService::validateUrl($infoUrl)) {
            $_SESSION['admin_error'] = "Будь ласка, введіть коректний URL.";
            header("Location: /admin");
            exit();
        }

        $city = new City(
            id: 0,
            name: htmlspecialchars(trim($_POST['name'] ?? '')),
            positionX: (float) ($_POST['positionX'] ?? 0),
            positionY: (float) ($_POST['positionY'] ?? 0),
            infoUrl: $infoUrl
        );

        if ($this->cityRepository->add($city)) {
            $_SESSION['admin_message'] = "Місто '{$city->name}' успішно додано до бази!";
        } else {
            $_SESSION['admin_error'] = "Помилка! Можливо, це місто вже існує.";
        }

        header("Location: /admin");
        exit();
    }

    /**
     * Handle GET to download the CSV export
     */
    public function exportCsv(): void
    {
        if (ob_get_length()) {
            ob_clean();
        }

        $cities = $this->cityRepository->getAll();
        $date = date('d-m-Y');
        $rawFileName = "WeatherMaster Cities Backup {$date}.csv";
        $safeFileName = RegexService::replaceSpacesInFileName($rawFileName);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeFileName . '"');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // BOM for Excel UTF-8 support
        fputcsv($output, ['ID', 'Назва міста', 'Широта (X)', 'Довгота (Y)', 'Info URL']);

        foreach ($cities as $city) {
            fputcsv($output, [
                $city->id ?? '',
                $city->name ?? '',
                $city->positionX ?? '',
                $city->positionY ?? '',
                $city->infoUrl ?? ''
            ]);
        }

        fclose($output);
        exit();
    }
}