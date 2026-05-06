<?php
include_once "base.php";

abstract class AuthBase extends BasePage
{
    public function __construct(string $title)
    {
        parent::__construct($title);
        $this->verifyAuthentication();
    }

    /**
     * Checks if the admin session exists. If not, kicks the user to the login page.
     */
    private function verifyAuthentication(): void
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            $loginPath = PathHelper::getAbsolutePath("auth/login.php");
            header("Location: " . $loginPath);
            exit();
        }
    }
}