<?php
include_once "../data/database.php";
include_once "../repositories/adminUserRepository.php";
include_once "../base.php";
include_once "../pathHelper.php";

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
                            <label for="login" class="form-label">Логін*</label>
                            <input type="text" class="form-control" id="login" name="login" placeholder="Введіть ваш логін: " required>
                        </div>
                        <div class="mb-2">
                            <label for="password" class="form-label">Пароль*</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Введіть ваш пароль: " required>
                        </div>
                        <div id="requiredFieldsHint" class="form-text mb-2">All fields marked with * are required.</div>
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
            header("Location: admin.php");
            exit();
        }
        $content = $this->getContent();
        $this->printBasePage($content);
    }

    public function post(): void
    {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        $db = new Database();
        $adminRepository = new AdminUserRepository($db);

        $user = $adminRepository->findByLogin($login);

        if ($user && password_verify($password, $user->passwordHash)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_login'] = $user->login;

            header("Location: " . PathHelper::getAbsolutePath("index.php"));
            exit();
        } else {
            $this->error = "Невірний логін або пароль!";
        }
        $this->get();
    }
}

$loginPage = new LoginPage();
$loginPage->render();