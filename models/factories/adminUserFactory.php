<?php
namespace WeatherMaster\Models\Factories;

include_once __DIR__ . "/../AdminUser.php";
use WeatherMaster\Models\AdminUser;
class AdminUserFactory
{
    public static function instantiate(string $login, string $email, string $rawPassword, string $phoneNumber): AdminUser
    {
        $registeredAt = date("Y-m-d H:i:s");
        $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);

        $user = new AdminUser();
        $user->login = trim($login);
        $user->email = trim($email);
        $user->phoneNumber = trim($phoneNumber);
        $user->passwordHash = $passwordHash;
        $user->registeredAt = $registeredAt;

        return $user;
    }
}