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

    public function get_header(): string
    {
        $indexPath = PathHelper::get_absolute_path("index.php");
        $weeklyForecastPath = PathHelper::get_absolute_path("weeklyForecast.php");

        $loginPath = PathHelper::get_absolute_path("auth/login.php");
        $logoutPath = PathHelper::get_absolute_path("auth/logout.php");
        $addCityPath = PathHelper::get_absolute_path("admin/addCity.php");

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
            <div class="header__wrap py-4 d-flex flex-wrap justify-content-center">
                <a href="$indexPath" class="header__logo d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                    <svg class="bi me-2" width="40" height="32" aria-hidden="true"><use xlink:href="#bootstrap"></use></svg>
                    <span class="fs-4 header__logo-text">Weather Master</span>
                </a>
                <ul class="nav nav-pills">
                    <li class="nav-item"><a href="$indexPath" class="nav-link" aria-current="page">Головна</a></li>
                    <li class="nav-item"><a href="$weeklyForecastPath" class="nav-link">Щотижневий прогноз</a></li>
                    $actionsHTML
                </ul>
            </div>
        </div>
    </header>
HTML;
    }
    public function get_footer(): string
    {
        $indexPath = PathHelper::get_absolute_path("index.php");
        $weeklyForecastPath = PathHelper::get_absolute_path("weeklyForecast.php");

        $date = date("Y");
        return <<<HTML
    <footer class="footer py-3 mt-4"> 
        <div class="container">
            <ul class="nav justify-content-center border-bottom pb-3 mb-3"> 
                <li class="nav-item"><a href="$indexPath" class="nav-link px-2 text-body-secondary">Головна</a></li> 
                <li class="nav-item"><a href="$weeklyForecastPath" class="nav-link px-2 text-body-secondary">Щотижневий прогноз</a></li>
            </ul> 
            <p class="footer__copyright text-center text-body-secondary">© {$date} Weather Master</p>
        </div>
    </footer>
HTML;
    }
    /**
     * @param string $content
     */
    public function print_base_page($content): void
    {
        $stylePath = PathHelper::get_absolute_path("assets/css/style.css");
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
                    {$this->get_header()}
                    <main class="main">
                        {$content}
                    </main>
                    {$this->get_footer()}
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
