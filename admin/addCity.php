<?php
include_once "../data/database.php";
include_once "../models/city.php";
include_once "../repositories/cityRepository.php";
include_once "../authBase.php";
include_once "../pathHelper.php";

session_start();

class AddCityPage extends AuthBase
{
    public function __construct()
    {
        parent::__construct("Weather Master Add City");
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
                        <input type="number" step="0.0001" class="form-control" id="positionX" name="positionX" min="-90" max="90" value="0"
                            required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="positionY" class="form-label">Довгота (Longitude )*</label>
                        <input type="number" step="0.0001" class="form-control" id="positionY" name="positionY" min="-180" max="180" value="0"
                            required>
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
        $city = new City(htmlspecialchars($_POST['name'] ?? ''), (float) ($_POST['positionX'] ?? 0), (float) ($_POST['positionY'] ?? 0));

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