<?php
class AdminUser
{
    public function __construct(
        public int $id = 0,
        public string $login = "",
        public string $passwordHash = "",
        public string $email = "",
        public string $registeredAt = ""
    ) {
    }
    public const TABLE_NAME = "admin_user";
}