<?php
namespace WeatherMaster\Controllers;

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
        $alertFilePath = __DIR__ . "/../data/alert.txt";

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
        extract($data);

        $viewPath = __DIR__ . '/../views/' . $view . '.phtml';
        $layoutPath = __DIR__ . '/../views/layouts/mainLayout.phtml';

        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }
        if (!file_exists($layoutPath)) {
            die("Layout not found: " . $layoutPath);
        }

        ob_start();
        require_once $viewPath;
        $content = ob_get_clean();
        require_once $layoutPath;
    }
}