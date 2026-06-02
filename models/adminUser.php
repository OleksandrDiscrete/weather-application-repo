<?php
namespace WeatherMaster\Models;
class AdminUser
{
    public function __construct(
        public int $id = 0,
        public string $login = "",
        public string $passwordHash = "",
        public string $email = "",
        public string $phoneNumber = "",
        public string $registeredAt = ""
    ) {
    }
    /**
     * The name of the model in the database
     * @var string
     */
    public const TABLE_NAME = "admin_user";
}