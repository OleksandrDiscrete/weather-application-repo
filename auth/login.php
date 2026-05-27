<?php

namespace WeatherMaster\Auth;

include_once "../data/database.php";
include_once "../repositories/adminUserRepository.php";
include_once "../services/regexService.php";
include_once "../helpers/pathHelper.php";
include_once "../base.php";

use WeatherMaster\BasePage;
use WeatherMaster\Data\Database;
use WeatherMaster\Helpers\PathHelper;
use WeatherMaster\Repositories\AdminUserRepository;
use WeatherMaster\Services\RegexService;

session_start();

class LoginPage extends BasePage
{
    public function __construct()
    {
        parent::__construct("Weather Master Login");
    }

    private function getContent(): string
    {
        $errorHtml = '';
        if ($this->error) {
            $errorHtml = '<div class="alert alert-danger">' . htmlspecialchars($this->error) . '</div>';
        }
        $actionPath = PathHelper::getAbsolutePath("auth/login.php");
        return <<<HTML
    <section class="auth py-5">
        <div class="container">
            <div class="auth__wrap">
                <div class="card mx-auto px-4 py-3">
                    <h2 class="text-center mb-4">Увійдіть у систему</h2>
                    $errorHtml
                    <form method="POST" action="$actionPath">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="phoneNumber" class="form-label">Номер телефону (UA)*</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="+380000000000" required>
                        </div>
                        <div class="mb-2">
                            <label for="password" class="form-label">Пароль*</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Введіть ваш пароль: " required>
                        </div>
                        <div id="requiredFieldsHint" class="form-text mb-2">Всі поля позначені (*) є обов'язковими.</div>
                        <button type="submit" class="btn btn-primary w-100">Увійти</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    HTML;
    }

    public function get(): void
    {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header("Location: " . PathHelper::getAbsolutePath("admin/"));
            exit();
        }
        $content = $this->getContent();
        $this->printBasePage($content);
    }

    private function isValidInput(string $email, string $password, string $phoneNumber): bool
    {
        if (!RegexService::validateEmail($email)) {
            $this->error = "Будь ласка, введіть коректний email.";
            return false;
        }
        if (!RegexService::validateUaPhone($phoneNumber)) {
            $this->error = "Номер телефону повинен бути у форматі +380000000000.";
            return false;
        }
        if (empty($password)) {
            $this->error = "Будь ласка, введіть пароль.";
            return false;
        }
        return true;
    }

    public function post(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $phoneNumber = $_POST['phoneNumber'] ?? '';

        if (!$this->isValidInput($email, $password, $phoneNumber)) {
            $this->get();
            return;
        }

        $db = new Database();
        $adminRepository = new AdminUserRepository($db);

        $user = $adminRepository->findByEmailAndPhoneNumber($email, $phoneNumber);

        if (empty($user)) {
            $this->error = "Невірний email або номер телефону!";
        } else if (!password_verify($password, $user->passwordHash)) {
            $this->error = "Невірний пароль!";
        } else {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_login'] = $user->login;
            header("Location: " . PathHelper::getAbsolutePath("admin/"));
            exit();
        }
        $this->get();
    }
}

$loginPage = new LoginPage();
$loginPage->render();