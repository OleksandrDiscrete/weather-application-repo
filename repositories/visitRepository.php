<?php

namespace WeatherMaster\Repositories;

include_once "baseRepository.php";
include_once __DIR__ . "/../models/visitLog.php";

use PDO;
use PDOException;
use WeatherMaster\Data\Database;
use WeatherMaster\Models\VisitLog;

/**
 * @extends BaseRepository<VisitLog>
 */
class VisitRepository extends BaseRepository
{
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function initTable(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS " . VisitLog::TABLE_NAME . " (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    page TEXT NOT NULL,
                    ip_address TEXT NOT NULL,
                    user_agent TEXT,
                    visited_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo "Error creating visit_log table: " . $e->getMessage();
        }
    }

    /**
     * @param VisitLog $item
     */
    public function add($item): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO " . VisitLog::TABLE_NAME .
                " (page, ip_address, user_agent) VALUES (:page, :ip_address, :user_agent)"
            );
            $stmt->bindParam(':page', $item->page);
            $stmt->bindParam(':ip_address', $item->ipAddress);
            $stmt->bindParam(':user_agent', $item->userAgent);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function remove(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM " . VisitLog::TABLE_NAME . " WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function seed(): void
    {
        // Лічильник не потребує початкових даних
    }

    
    public function getTotalCount(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM " . VisitLog::TABLE_NAME);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

  
    public function getUniqueVisitorsCount(): int
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT ip_address) FROM " . VisitLog::TABLE_NAME);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

   
    public function getTodayCount(): int
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT COUNT(*) FROM " . VisitLog::TABLE_NAME .
                " WHERE DATE(visited_at) = DATE('now')"
            );
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    
    public function getPageStats(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT page, COUNT(*) as visits FROM " . VisitLog::TABLE_NAME .
                " GROUP BY page ORDER BY visits DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    
    public function getRecent(int $limit = 10): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM " . VisitLog::TABLE_NAME .
                " ORDER BY visited_at DESC LIMIT :limit"
            );
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}