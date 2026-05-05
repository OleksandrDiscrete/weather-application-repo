<?php
include_once "base.php";

abstract class AuthBase extends BasePage
{
    public function __construct(string $title)
    {
        parent::__construct($title);
        $this->verify_authentication();
    }
    /**
     * Checks if the admin session exists. If not, kicks the user to the login page.
     */
    private function verify_authentication(): void
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            $loginPath = PathHelper::get_absolute_path("auth/login.php");
            header("Location: " . $loginPath);
            exit();
        }
    }
}