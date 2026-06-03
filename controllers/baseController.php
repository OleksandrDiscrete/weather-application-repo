<?php
namespace WeatherMaster\Controllers;

include_once __DIR__ . "/../data/database.php";
include_once __DIR__ . "/../repositories/visitRepository.php";
include_once __DIR__ . "/../services/regexService.php";
include_once __DIR__ . "/../models/factories/visitLogFactory.php";

use WeatherMaster\Data\Database;
use WeatherMaster\Repositories\VisitRepository;
use WeatherMaster\Services\RegexService;
use WeatherMaster\Models\Factories\VisitLogFactory;

abstract class BaseController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->trackVisit();
        }
    }

    private function trackVisit(): void
    {
        try {
            $db = new Database();
            $visitRepo = new VisitRepository($db);
            $visitRepo->initTable();
            $visitRepo->add(VisitLogFactory::instantiate());
        } catch (\Throwable $e) {
        }
    }

    protected function getApplicationAlert(): string
    {
        $alertFilePath = __DIR__ . "/data/alert.txt";

        if (file_exists($alertFilePath)) {
            $rawText = file_get_contents($alertFilePath);

            if (trim($rawText) !== '') {
                $formattedAlert = RegexService::textToHtml($rawText);
                return $formattedAlert;
            }
        }
        return "";
    }

    /**
     * @param string $view Path to the view file (e.g., 'home/index')
     * @param array $data Associative array of variables to pass to the view
     */
    protected function render(string $view, array $data = []): void
    {
        // 1. Extract data so the specific view can use the variables
        extract($data);

        $viewPath = __DIR__ . '/../views/' . $view . '.phtml';
        $layoutPath = __DIR__ . '/../views/layouts/mainLayout.phtml';

        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }
        if (!file_exists($layoutPath)) {
            die("Layout not found: " . $layoutPath);
        }

        // 2. START Output Buffering
        ob_start();

        // 3. Require the specific view (e.g., home/index.phtml). 
        // Because of ob_start(), it won't be printed to the screen.
        require_once $viewPath;

        // 4. Grab everything that was just generated and save it to $content, 
        // then clean and close the buffer.
        $content = ob_get_clean();

        // 5. Finally, require the main layout. 
        // The layout file will naturally have access to the $content variable we just created!
        require_once $layoutPath;
    }
}