<?php

include_once "baseRepository.php";
include_once "../models/adminUser.php";

/**
 * @extends BaseRepository<AdminUser>
 */
class AdminUserRepository extends BaseRepository
{

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function initTable(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS " . AdminUser::TABLE_NAME . " (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    login TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
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
                "(login, password_hash, email) VALUES (:login, :password_hash, :email)");

            $stmt->bindParam(':login', $item->login);
            $stmt->bindParam(':password_hash', $item->passwordHash);
            $stmt->bindParam(':email', $item->email);

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
     * Finds an AdminUser by their login name.
     * @param string $login
     * @return AdminUser|null
     */
    public function findByLogin(string $login): ?AdminUser
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM " . AdminUser::TABLE_NAME . " WHERE login = :login LIMIT 1");
            $stmt->bindParam(':login', $login);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $user = new AdminUser($data['id'], $data['login'], $data['password_hash'], $data['email'], $data['registered_at']);
                return $user;
            }
            return null;
        } catch (PDOException $e) {
            die("Error fetching user: " . $e->getMessage());
        }
    }
}

