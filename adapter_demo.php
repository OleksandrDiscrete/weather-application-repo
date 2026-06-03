<?php
namespace WeatherMaster;

include_once __DIR__ . "/helpers/pathHelper.php";
include_once __DIR__ . "/base.php";

include_once __DIR__ . "/data/adapters/mySqlDatabaseAdapter.php"; 

use WeatherMaster\BasePage;
use WeatherMaster\Data\Adapters\MySqlDatabaseAdapter;

class AdapterDemoPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Патерн Adapter");
    }

    public function get(): void
    {
        $tableRows = "";
        $errorHtml = "";

        try {
            $adapter = new MySqlDatabaseAdapter("127.0.0.1", "weather_master_db", "root", "");

            $adapter->connect();

            $cities = $adapter->fetchMany("SELECT * FROM cities");
            
            if (!empty($cities)) {
                foreach ($cities as $city) {
                    $tableRows .= "<tr>
                        <td>{$city['id']}</td>
                        <td>{$city['name']}</td>
                        <td>{$city['positionX']}</td>
                        <td>{$city['positionY']}</td>
                    </tr>";
                }
            } else {
                $tableRows = "<tr><td colspan='4' class='text-center text-muted'>Таблиця міст порожня</td></tr>";
            }
            
            $adapter->disconnect();

        } catch (\Throwable $e) {
            $errorHtml = "<div class='alert alert-danger'>Помилка БД: " . $e->getMessage() . "</div>";
        }

        $content = <<<HTML
        <section class="py-5">
            <div class="container">
                <h1 class="mb-4 text-center"><i class="bi bi-hdd-network text-info me-2"></i>Шаблон проєктування Adapter</h1>
                <p class="text-center text-muted mb-5">Ця сторінка демонструє роботу з MySQL базою даних через універсальний клас-адаптер.</p>
                
                {$errorHtml}

                <div class="card shadow-sm mx-auto" style="max-width: 800px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Список міст (з MySQL через Адаптер Богдана)</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Місто</th>
                                    <th>Широта</th>
                                    <th>Довгота</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
HTML;

        $this->printBasePage($content);
    }

    public function post(): void
    {
        $this->get();
    }
}

$page = new AdapterDemoPage();
$page->render();