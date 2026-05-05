<?php
class AdminUser
{
    public function __construct(
        public int $id = 0,
        public string $login = "",
        public string $password_hash = "",
        public string $email = "",
        public string $registered_at = ""
    ) {
    }
    public const TABLE_NAME = "admin_user";
}