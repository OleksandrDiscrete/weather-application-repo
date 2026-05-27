<?php

namespace WeatherMaster\Admin;

include_once "../data/database.php";
include_once "../models/city.php";
include_once "../repositories/cityRepository.php";
include_once "../repositories/visitRepository.php";
include_once "../models/visitLog.php";
include_once "../helpers/pathHelper.php";
include_once "../authBase.php";

use WeatherMaster\AuthBase;
use WeatherMaster\Models\City;
use WeatherMaster\Data\Database;
use WeatherMaster\Helpers\PathHelper;
use WeatherMaster\Repositories\CityRepository;
use WeatherMaster\Repositories\VisitRepository;

session_start();

class AddCityPage extends AuthBase
{
    public function __construct()
    {
        parent::__construct("Weather Master Add City");
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
        $actionPath = PathHelper::getAbsolutePath("admin/addCity.php");
        $visitStatsHtml = $this->getVisitStatsHtml();

        return <<<HTML
    <section class="admin py-5">
        <div class="container">
            <div class="admin__wrap">
                <div class="text-content mb-4">
                    <h1 class="mb-3">Вітаємо, <span class="navbar-text me-3">
                    $adminName!
                    </span></h1>
                </div>

                $errorHtml
                $messageHtml

                {$visitStatsHtml}

        <div class="card shadow p-4 mx-auto">
            <h2 class="mb-4">Додати нове місто</h2>
            <form method="POST" action="$actionPath">
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
                <div id="requiredFieldsHint" class="form-text mb-2">All fields marked with * are required.</div>
                <button type="submit" class="btn btn-primary">Зберегти місто</button>
            </form>
        </div>

    </div>
        </div>
    </section>
    HTML;
    }

    public function get(): void
    {
        $content = $this->getContent();
        $this->printBasePage($content);
    }

    public function post(): void
    {
        $city = new City(
            name: htmlspecialchars(trim($_POST['name'] ?? '')),
            positionX: (float) ($_POST['positionX'] ?? 0),
            positionY: (float) ($_POST['positionY'] ?? 0)
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

$addCityPage = new AddCityPage();
$addCityPage->render();