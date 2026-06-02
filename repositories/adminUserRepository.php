<?php
namespace WeatherMaster\Repositories;

include_once "baseRepository.php";
include_once __DIR__ . "/../data/database.php";
include_once __DIR__ . "/../models/adminUser.php";
include_once __DIR__ . "/../models/factories/adminUserFactory.php";

use WeatherMaster\Data\DatabaseInterface;
use WeatherMaster\Models\AdminUser;
use WeatherMaster\Models\Factories\AdminUserFactory;

/**
 * @extends BaseRepository<AdminUser>
 */
class AdminUserRepository extends BaseRepository
{

    public function __construct(DatabaseInterface $db)
    {
        parent::__construct($db);
        $this->initAndSeed();
    }

    public function initTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . AdminUser::TABLE_NAME . " (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    login TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    phone_number TEXT NOT NULL UNIQUE,
                    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
        $this->db->execute($sql);
    }

    /**
     * @param AdminUser $item
     */
    public function add($item): bool
    {
        $sql = "INSERT INTO " .
            AdminUser::TABLE_NAME .
            "(login, password_hash, email, phone_number) VALUES (:login, :password_hash, :email, :phoneNumber)";
        return $this->db->executeWithParameters($sql, [
            'login' => $item->login,
            'password_hash' => $item->passwordHash,
            'email' => $item->email,
            'phoneNumber' => $item->phoneNumber
        ]);
    }

    public function remove(int $id): bool
    {
        $sql = "DELETE FROM " . AdminUser::TABLE_NAME . " WHERE id = :id";
        return $this->db->executeWithParameters($sql, ['id' => $id]);
    }

    /**
     * Seeds the database with a default admin user if the table is empty.
     */
    public function seed(): void
    {
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM " . AdminUser::TABLE_NAME);
        if ($count == 0) {
            $admin = AdminUserFactory::instantiate("admin", "admin@weathermaster.ua", "SuperSecretPassword123!", "+380000000000");
            $success = $this->add($admin);
            if ($success) {
                echo "Seed successful: Default admin account created.\n";
            }
        }
    }
    /**
     * Finds an AdminUser by their email and phone number.
     * @param string $email
     * @param string $phoneNumber
     * @return AdminUser|null
     */
    public function findByEmailAndPhoneNumber(string $email, string $phoneNumber): ?AdminUser
    {
        $sql = "SELECT * FROM " . AdminUser::TABLE_NAME . " WHERE email = :email AND phone_number = :phoneNumber LIMIT 1";
        $data = $this->db->fetchOne($sql, ['email' => $email, 'phoneNumber' => $phoneNumber]);
        if ($data) {
            $user = new AdminUser($data['id'], $data['login'], $data['password_hash'], $data['email'], $data['phone_number'], $data['registered_at']);
            return $user;
        }
        return null;
    }
}

