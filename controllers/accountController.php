<?php
namespace WeatherMaster\Controllers;

use WeatherMaster\Data\Database;
use WeatherMaster\Repositories\AdminUserRepository;
use WeatherMaster\Services\RegexService;

class AccountController extends BaseController
{
    private AdminUserRepository $adminRepository;

    public function __construct()
    {
        parent::__construct();
        $db = new Database();
        $this->adminRepository = new AdminUserRepository($db);
    }

    /**
     * Handles GET request to display the login form.
     */
    public function getLogin(): void
    {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header("Location: /admin");
            exit();
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        $oldInput = $_SESSION['login_old_input'] ?? ['email' => '', 'phoneNumber' => ''];
        unset($_SESSION['login_old_input']);

        $this->render('account/login', [
            'pageTitle' => 'Weather Master Login',
            'error' => $error,
            'oldInput' => $oldInput
        ]);
    }

    /**
     * Handles POST request to process the login form.
     */
    public function processLogin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $phoneNumber = trim($_POST['phoneNumber'] ?? '');
        $password = $_POST['password'] ?? '';

        $_SESSION['login_old_input'] = [
            'email' => htmlspecialchars($email),
            'phoneNumber' => htmlspecialchars($phoneNumber)
        ];

        if (!RegexService::validateEmail($email)) {
            $this->redirectWithError("Будь ласка, введіть коректний email.");
        }
        if (!RegexService::validateUaPhone($phoneNumber)) {
            $this->redirectWithError("Номер телефону повинен бути у форматі +380000000000.");
        }
        if (empty($password)) {
            $this->redirectWithError("Будь ласка, введіть пароль.");
        }

        $user = $this->adminRepository->findByEmailAndPhoneNumber($email, $phoneNumber);
        if (!$user) {
            $this->redirectWithError("Невірний email або номер телефону!");
        }
        if (!password_verify($password, $user->passwordHash)) {
            $this->redirectWithError("Невірний пароль!");
        }

        unset($_SESSION['login_old_input']);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login'] = $user->login;

        header("Location: /admin");
        exit();
    }

    /**
     * Handles GET request to log the user out.
     */
    public function logout(): void
    {
        session_start();
        session_destroy();

        header("Location: /");
        exit();
    }

    /**
     * Helper to set an error and redirect back to the login page (PRG pattern).
     */
    private function redirectWithError(string $message): void
    {
        $_SESSION['login_error'] = $message;
        header("Location: /auth/login");
        exit();
    }
}