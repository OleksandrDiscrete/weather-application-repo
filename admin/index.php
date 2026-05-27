<?php

namespace WeatherMaster\Admin;

include_once "../data/database.php";
include_once "../models/city.php";
include_once "../repositories/cityRepository.php";
include_once "../repositories/visitRepository.php";
include_once "../services/regexService.php";
include_once "../models/visitLog.php";
include_once "../helpers/pathHelper.php";
include_once "../authBase.php";

use WeatherMaster\AuthBase;
use WeatherMaster\Models\City;
use WeatherMaster\Data\Database;
use WeatherMaster\Helpers\PathHelper;
use WeatherMaster\Repositories\CityRepository;
use WeatherMaster\Repositories\VisitRepository;
use WeatherMaster\Services\RegexService;

session_start();

class AdminPage extends AuthBase
{
    public function __construct()
    {
        parent::__construct("Weather Master Admin");
    }

    private function getVisitStatsHtml(): string
    {
        try {
            $db = new Database();
            $visitRepo = new VisitRepository($db);
            $visitRepo->initTable();

            $total = $visitRepo->getTotalCount();
            $unique = $visitRepo->getUniqueVisitorsCount();
            $today = $visitRepo->getTodayCount();
            $pageStats = $visitRepo->getPageStats();
            $recent = $visitRepo->getRecent(5);

            $pageRows = '';
            foreach ($pageStats as $row) {
                $page = htmlspecialchars($row['page']);
                $visits = (int) $row['visits'];
                $pageRows .= <<<HTML
                    <tr>
                        <td><code>{$page}</code></td>
                        <td><span class="badge bg-primary">{$visits}</span></td>
                    </tr>
                HTML;
            }

            $recentRows = '';
            foreach ($recent as $row) {
                $page = htmlspecialchars($row['page']);
                $ip = htmlspecialchars($row['ip_address']);
                $time = htmlspecialchars($row['visited_at']);
                $recentRows .= <<<HTML
                    <tr>
                        <td><code>{$page}</code></td>
                        <td>{$ip}</td>
                        <td>{$time}</td>
                    </tr>
                HTML;
            }

            return <<<HTML
            <div class="card shadow p-4 mx-auto mb-4">
                <h2 class="mb-4"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Лічильник відвідувань</h2>

                <div class="row text-center mb-4 g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="fs-1 fw-bold text-primary">{$total}</div>
                            <div class="text-muted">Всього відвідувань</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="fs-1 fw-bold text-success">{$unique}</div>
                            <div class="text-muted">Унікальних відвідувачів</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="fs-1 fw-bold text-warning">{$today}</div>
                            <div class="text-muted">Відвідувань сьогодні</div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Статистика по сторінках</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Сторінка</th>
                                <th>Відвідувань</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$pageRows}
                        </tbody>
                    </table>
                </div>

                <h5 class="mb-3">Останні 5 відвідувань</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Сторінка</th>
                                <th>IP-адреса</th>
                                <th>Час</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$recentRows}
                        </tbody>
                    </table>
                </div>
            </div>
            HTML;

        } catch (\Throwable $e) {
            return '<div class="alert alert-warning">Не вдалося завантажити статистику відвідувань.</div>';
        }
    }

    private function getContent(): string
    {
        $errorHtml = '';
        if ($this->error) {
            $errorHtml = '<div class="alert alert-danger">' . htmlspecialchars($this->error) . '</div>';
        }

        $messageHtml = '';
        if ($this->message) {
            $messageHtml = '<div class="alert alert-success">' . htmlspecialchars($this->message) . '</div>';
        }

        $adminName = isset($_SESSION['admin_login']) ? htmlspecialchars($_SESSION['admin_login']) : '';
        $actionPath = PathHelper::getAbsolutePath("admin/index.php");
        $visitStatsHtml = $this->getVisitStatsHtml();

        $alertFilePath = __DIR__ . "/../data/alert.txt";
        $currentAlertHtml = '';
        if (file_exists($alertFilePath)) {
            $rawText = file_get_contents($alertFilePath);
            $currentAlertHtml = RegexService::textToHtml($rawText);
            $currentAlertHtml = str_ireplace('<br>', "\n", $currentAlertHtml);
        }

        return <<<HTML
    <section class="admin py-5">
        <div class="container">
            <div class="admin__wrap">
                <div class="text-content mb-4 d-flex justify-content-between align-items-center">
                    <h1 class="mb-0">Вітаємо, <span class="navbar-text">$adminName!</span></h1>
                    <a href="?export=csv" class="btn btn-outline-success">
                        <i class="bi bi-download me-2"></i> Експортувати міста
                    </a>
                </div>
                $errorHtml
                $messageHtml
                <div class="card shadow p-4 mx-auto mb-4 border-warning">
                    <h2 class="mb-3 text-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>Система попереджень</h2>
                    <form method="POST" action="$actionPath">
                        <input type="hidden" name="action" value="save_alert">
                        <div class="mb-3">
                            <label for="alert_html" class="form-label">Введіть HTML-код попередження (використовуйте &lt;strong&gt; та &lt;em&gt;)</label>
                            <textarea class="form-control" id="alert_html" name="alert_html" rows="3" placeholder="Наприклад: <strong>Увага!</strong> Очікується <em>сильний вітер</em>.">{$currentAlertHtml}</textarea>
                            <div class="form-text">Після збереження цей HTML буде перетворено у текстовий файл за допомогою регулярних виразів. Щоб видалити банер, просто очистіть це поле.</div>
                        </div>
                        <button type="submit" class="btn btn-warning">Зберегти попередження</button>
                    </form>
                </div>
                $visitStatsHtml
                <div class="card shadow p-4 mx-auto">
                    <h2 class="mb-4">Додати нове місто</h2>
                    <form method="POST" action="$actionPath">
                        <input type="hidden" name="action" value="save_city">
                        <div class="mb-3">
                            <label for="name" class="form-label">Назва міста*</label>
                            <input type="text" class="form-control" id="name" name="name" minlength="2" maxlength="100" placeholder="Введіть назву міста: " required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="positionX" class="form-label">Широта (Latitude)*</label>
                                <input type="number" step="0.0001" class="form-control" id="positionX" name="positionX" min="-90" max="90" value="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="positionY" class="form-label">Довгота (Longitude)*</label>
                                <input type="number" step="0.0001" class="form-control" id="positionY" name="positionY" min="-180" max="180" value="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="infoUrl" class="form-label">URL інформації про місто</label>
                            <input type="url" class="form-control" id="infoUrl" name="infoUrl" placeholder="Введіть URL інформації про місто: ">
                        </div>
                        <div id="requiredFieldsHint" class="form-text mb-2">All fields marked with * are required.</div>
                        <button type="submit" class="btn btn-primary">Зберегти місто</button>
                    </form>
                </div>

            </div>
        </div>
    </section>
    HTML;
    }

    /**
     * Generates and downloads the CSV file containing all cities.
     */
    private function exportCitiesCsv(): void
    {
        $db = new Database();
        $cityRepo = new CityRepository($db);
        $cities = $cityRepo->getAll();

        $date = date('d-m-Y');
        $rawFileName = "WeatherMaster Cities Backup {$date}.csv";

        $safeFileName = RegexService::replaceSpacesInFileName($rawFileName);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeFileName . '"');

        $output = fopen('php://output', 'w');

        fputs($output, "\xEF\xBB\xBF");
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

    public function get(): void
    {
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->exportCitiesCsv();
            return;
        }

        $content = $this->getContent();
        $this->printBasePage($content);
    }

    private function isValidInput(string $infoUrl): bool
    {
        if (!RegexService::validateUrl($infoUrl)) {
            $this->error = "Будь ласка, введіть коректний URL.";
            return false;
        }
        return true;
    }

    /**
     * Handles saving the alert text file (HTML -> Text via Regex)
     */
    private function saveAlertAction(): void
    {
        $incomingHtmlFromAdmin = $_POST['alert_html'] ?? '';
        $cleanTextForFile = RegexService::htmlToText($incomingHtmlFromAdmin);
        $alertFilePath = __DIR__ . "/../data/alert.txt";

        if (file_put_contents($alertFilePath, $cleanTextForFile) !== false) {
            $this->message = "Попередження успішно збережено!";
        } else {
            $this->error = "Помилка збереження файлу. Перевірте права доступу до папки data/.";
        }

        $this->get();
    }

    /**
     * Handles routing POST requests based on the hidden 'action' field
     */
    public function post(): void
    {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_alert') {
            $this->saveAlertAction();
            return;
        }

        $infoUrl = trim($_POST['infoUrl'] ?? '');
        if (!$this->isValidInput($infoUrl)) {
            $this->get();
            return;
        }

        $city = new City(
            id: 0,
            name: htmlspecialchars(trim($_POST['name'] ?? '')),
            positionX: (float) ($_POST['positionX'] ?? 0),
            positionY: (float) ($_POST['positionY'] ?? 0),
            infoUrl: $infoUrl
        );

        $db = new Database();
        $cityRepo = new CityRepository($db);

        if ($cityRepo->add($city)) {
            $this->message = "Місто '{$city->name}' успішно додано до бази!";
        } else {
            $this->error = "Помилка! Можливо, це місто вже існує.";
        }

        $this->get();
    }
}

$adminPage = new AdminPage();
$adminPage->render();