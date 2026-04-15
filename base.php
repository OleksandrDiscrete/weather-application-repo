<?php
abstract class BasePage
{
    private $title;
    /**
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    public function get_header(): string
    {
        return <<<HTML
    <header class="header border-bottom">
        <div class="container">
            <div class="header__wrap py-4 d-flex flex-wrap justify-content-center">
                <a href="./index.php" class="header__logo d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                    <svg class="bi me-2" width="40" height="32" aria-hidden="true"><use xlink:href="#bootstrap"></use></svg>
                    <span class="fs-4 header__logo-text">Weather Master</span>
                </a>
                <ul class="nav nav-pills">
                    <li class="nav-item"><a href="./index.php" class="nav-link active" aria-current="page">Головна</a></li>
                    <li class="nav-item"><a href="./weeklyForecast.php" class="nav-link">Щотижневий прогноз</a></li>
                </ul>
            </div>
        </div>
    </header>
HTML;
    }
    public function get_footer(): string
    {
        $date = date("Y");
        return <<<HTML
    <footer class="footer py-3 mt-4"> 
        <div class="container">
            <ul class="nav justify-content-center border-bottom pb-3 mb-3"> 
                <li class="nav-item"><a href="./index.php" class="nav-link px-2 text-body-secondary">Головна</a></li> 
                <li class="nav-item"><a href="./weeklyForecast.php" class="nav-link px-2 text-body-secondary">Щотижневий прогноз</a></li>
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
        echo <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <title>{$this->title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                <link rel="stylesheet" href="./assets/css/style.css">   
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
