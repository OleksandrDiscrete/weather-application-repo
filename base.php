<?php

namespace WeatherMaster;

include_once __DIR__ . "/helpers/pathHelper.php";
include_once __DIR__ . "/data/database.php";
include_once __DIR__ . "/repositories/visitRepository.php";
include_once __DIR__ . "/models/visitLog.php";
include_once __DIR__ . "/models/factories/visitLogFactory.php";
include_once __DIR__ . "/services/regexService.php";

use WeatherMaster\Helpers\PathHelper;
use WeatherMaster\Data\Database;
use WeatherMaster\Repositories\VisitRepository;
use WeatherMaster\Services\RegexService;
use WeatherMaster\Models\Factories\VisitLogFactory;
// use WeatherMaster\Models\VisitLog;

session_start();

abstract class BasePage
{
    protected string $error = "";
    protected string $message = "";
    private string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    private function trackVisit(): void
    {
        try {
            $db = new Database();
            $visitRepo = new VisitRepository($db);
            $visitRepo->initTable();

            $visitRepo->add(VisitLogFactory::instantiate());
        } catch (\Throwable $e) {
            // Не переривати сторінку якщо лічильник впав
        }
    }

    protected function getAlert(): string
    {
        $alertHtml = '';

        $alertFilePath = __DIR__ . "/data/alert.txt";
        if (file_exists($alertFilePath)) {
            $rawText = file_get_contents($alertFilePath);

            if (trim($rawText) !== '') {
                $formattedAlert = RegexService::textToHtml($rawText);
                $alertHtml = <<<HTML
                    <div class="alert alert-warning shadow-sm mt-4 mb-4 text-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {$formattedAlert}
                    </div>
                HTML;
            }
        }
        return $alertHtml;
    }

    public function getHeader(): string
    {
        $indexPath = PathHelper::getAbsolutePath("index.php");
        $weeklyForecastPath = PathHelper::getAbsolutePath("weeklyForecast.php");
        $loginPath = PathHelper::getAbsolutePath("auth/login.php");
        $logoutPath = PathHelper::getAbsolutePath("auth/logout.php");
        $adminPath = PathHelper::getAbsolutePath("admin/");

        $actionsHTML = isset($_SESSION['admin_logged_in']) ? <<<HTML
                    <li class="nav-item"><a href="$adminPath" class="nav-link">Адміністрування</a></li>
                    <li class="nav-item"><a href="$logoutPath" class="nav-link active-danger">Вийти</a></li>
HTML
            : <<<HTML
                    <li class="nav-item"><a href="$loginPath" class="nav-link active">Увійти</a></li>
HTML;

        return <<<HTML
    <header class="header border-bottom">
        <div class="container">
            <div class="header__wrap py-4">
                <a href="$indexPath" class="header__logo d-flex align-items-center link-body-emphasis text-decoration-none">
                    <i class="bi bi-umbrella header__logo-icon" aria-hidden="true"></i>
                    <span class="fs-4 header__logo-text">Weather Master</span>
                </a>
                <nav>
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a href="$indexPath" class="nav-link" aria-current="page">Головна</a></li>
                        <li class="nav-item"><a href="$weeklyForecastPath" class="nav-link">Щотижневий прогноз</a></li>
                        $actionsHTML
                    </ul>
                </nav>
            </div>
        </div>
    </header>

HTML;
    }

    public function getFooter(): string
    {
        $indexPath = PathHelper::getAbsolutePath("index.php");
        $weeklyForecastPath = PathHelper::getAbsolutePath("weeklyForecast.php");
        $date = date("Y");

        return <<<HTML
    <footer class="footer py-4 mt-4"> 
        <div class="container">
            <nav>
                <ul class="nav justify-content-center border-bottom pb-3 mb-3"> 
                    <li class="nav-item"><a href="$indexPath" class="nav-link px-2">Головна</a></li> 
                    <li class="nav-item"><a href="$weeklyForecastPath" class="nav-link px-2">Щотижневий прогноз</a></li>
                </ul> 
            </nav>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center pt-2">
                <p class="footer__copyright">© {$date} Weather Master</p>
                <ul class="footer__social list-unstyled d-flex mb-0 mt-3 mt-sm-0">
                    <li class="ms-3">
                        <a class="footer__social-link" href="mailto:bohdan.shcherbak1@nure.ua" aria-label="Email нам">
                            <i class="bi bi-envelope-fill"></i>
                        </a>
                    </li>
                    <li class="ms-3">
                        <a class="footer__social-link" href="https://discord.gg/XJGmJ8Jg9" target="_blank" aria-label="Наш Discord сервер">
                            <i class="bi bi-discord"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

HTML;
    }

    public function printBasePage(string $content): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->trackVisit();
        }

        $stylePath = PathHelper::getAbsolutePath("assets/css/style.css");
        $faviconPath = PathHelper::getAbsolutePath("assets/favicon");
        $scriptsPath = PathHelper::getAbsolutePath("assets/scripts");

        echo <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <title>{$this->title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                <link rel="stylesheet" href="$stylePath">   
                <link rel="apple-touch-icon" sizes="180x180" href="$faviconPath/apple-touch-icon.png">
                <link rel="icon" type="image/png" sizes="32x32" href="$faviconPath/favicon-32x32.png">
                <link rel="icon" type="image/png" sizes="16x16" href="$faviconPath/favicon-16x16.png">
                <link rel="manifest" href="$faviconPath/site.webmanifest">
            </head>
            <body>
                <div class="wrapper">
                    {$this->getHeader()}
                    <main>
                        {$content}
                    </main>
                    {$this->getFooter()}
                    <div class="weather-toast-container toast-container position-fixed bottom-0 end-0">
                        <div id="liveAlertToast" class="toast text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header bg-warning text-dark border-bottom border-dark-subtle">
                                <i class="bi bi-bell-fill me-2"></i>
                                <strong class="me-auto">Нове повідомлення!</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body" id="toast-body-content"></div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
                <script src="$scriptsPath/alert.js" defer></script>
            </body>
            </html>
        HTML;
    }

    public function render(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
        } else {
            $this->get();
        }
    }

    abstract public function get(): void;
    abstract public function post(): void;
}