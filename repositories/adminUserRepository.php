<?php
namespace WeatherMaster\Repositories;

include_once __DIR__ . "/../data/database.php";
include_once "baseRepository.php";
include_once __DIR__ . "/../models/adminUser.php";

use PDO;
use PDOException;
use WeatherMaster\Data\Database;
use WeatherMaster\Models\AdminUser;

/**
 * @extends BaseRepository<AdminUser>
 */
class AdminUserRepository extends BaseRepository
{

    public function __construct(Database $db)
    {
        parent::__construct($db);
        $this->initAndSeed();
    }

    public function initTable(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS " . AdminUser::TABLE_NAME . " (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    login TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    phone_number TEXT NOT NULL UNIQUE,
                    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage();
        }
    }

    /**
     * @param AdminUser $item
     */
    public function add($item): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO " .
                AdminUser::TABLE_NAME .
                "(login, password_hash, email, phone_number) VALUES (:login, :password_hash, :email, :phoneNumber)");

            $stmt->bindParam(':login', $item->login);
            $stmt->bindParam(':password_hash', $item->passwordHash);
            $stmt->bindParam(':email', $item->email);
            $stmt->bindParam(':phoneNumber', $item->phoneNumber);

            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            die("Error inserting data: " . $e->getMessage());
        }
    }

    public function remove(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM " . AdminUser::TABLE_NAME . " WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error deleting data: " . $e->getMessage());
        }
    }

    /**
     * Seeds the database with a default admin user if the table is empty.
     */
    public function seed(): void
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM " . AdminUser::TABLE_NAME);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $admin = new AdminUser();
                $admin->login = "admin";
                $admin->phoneNumber = "+380000000000";
                $admin->email = "admin@weathermaster.ua";
                $admin->passwordHash = password_hash("SuperSecretPassword123!", PASSWORD_DEFAULT);

                $success = $this->add($admin);
                if ($success) {
                    echo "Seed successful: Default admin account created.\n";
                }
            }
        } catch (PDOException $e) {
            die("Error seeding admin user: " . $e->getMessage());
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
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM " . AdminUser::TABLE_NAME . " WHERE email = :email AND phone_number = :phoneNumber LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $user = new AdminUser($data['id'], $data['login'], $data['password_hash'], $data['email'], $data['phone_number'], $data['registered_at']);
                return $user;
            }
            return null;
        } catch (PDOException $e) {
            die("Error fetching data: " . $e->getMessage());
        }
    }
}

