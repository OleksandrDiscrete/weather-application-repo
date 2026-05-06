<?php
require_once "pathHelper.php";

session_start();

abstract class BasePage
{
    protected $error = "";
    protected $message = "";
    private string $title;

    /**
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    public function getHeader(): string
    {
        $indexPath = PathHelper::getAbsolutePath("index.php");
        $weeklyForecastPath = PathHelper::getAbsolutePath("weeklyForecast.php");

        $loginPath = PathHelper::getAbsolutePath("auth/login.php");
        $logoutPath = PathHelper::getAbsolutePath("auth/logout.php");
        $addCityPath = PathHelper::getAbsolutePath("admin/addCity.php");

        $actionsHTML = isset($_SESSION['admin_logged_in']) ? <<<HTML
                    <li class="nav-item"><a href="$addCityPath" class="nav-link">Додати місто</a></li>
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

    /**
     * @param string $content
     */
    public function printBasePage($content): void
    {
        $stylePath = PathHelper::getAbsolutePath("assets/css/style.css");
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
            </head>
            <body>
                <div class="wrapper">
                    {$this->getHeader()}
                    <main class="main">
                        {$content}
                    </main>
                    {$this->getFooter()}
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
            </body>
            </html>
        HTML;
    }

    public function render(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->get();
        }
    }

    public abstract function get();
    public abstract function post();
}