<?php

namespace WeatherMaster\Repositories;

include_once "baseRepository.php";
include_once __DIR__ . "/../models/visitLog.php";

use WeatherMaster\Data\DatabaseInterface;
use WeatherMaster\Models\VisitLog;

/**
 * @extends BaseRepository<VisitLog>
 */
class VisitRepository extends BaseRepository
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct($db);
        $db->connect();
    }

    public function initTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . VisitLog::TABLE_NAME . " (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    page TEXT NOT NULL,
                    ip_address TEXT NOT NULL,
                    user_agent TEXT,
                    visited_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ";
        $this->db->execute($sql);
    }

    /**
     * @param VisitLog $item
     */
    public function add($item): bool
    {
        $sql = "INSERT INTO " . VisitLog::TABLE_NAME .
            " (page, ip_address, user_agent) VALUES (:page, :ip_address, :user_agent)";
        return $this->db->executeWithParameters($sql, [
            'page' => $item->page,
            'ip_address' => $item->ipAddress,
            'user_agent' => $item->userAgent
        ]);
    }

    public function remove(int $id): bool
    {
        $sql = "DELETE FROM " . VisitLog::TABLE_NAME . " WHERE id = :id";
        return $this->db->executeWithParameters($sql, ['id' => $id]);
    }

    public function seed(): void
    {
        // Лічильник не потребує початкових даних
    }

    public function getTotalCount(): int
    {
        $sql = "SELECT COUNT(*) FROM " . VisitLog::TABLE_NAME;
        return $this->db->fetchColumn($sql);
    }

    public function getUniqueVisitorsCount(): int
    {
        $sql = "SELECT COUNT(DISTINCT ip_address) FROM " . VisitLog::TABLE_NAME;
        return $this->db->fetchColumn($sql);
    }

    public function getTodayCount(): int
    {
        $sql = "SELECT COUNT(*) FROM " . VisitLog::TABLE_NAME .
            " WHERE DATE(visited_at) = DATE('now')";
        return $this->db->fetchColumn($sql);
    }

    public function getPageStats(): array
    {
        $sql = "SELECT page, COUNT(*) as visits FROM " . VisitLog::TABLE_NAME .
            " GROUP BY page ORDER BY visits DESC";
        return $this->db->fetchMany($sql);
    }


    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT * FROM " . VisitLog::TABLE_NAME .
            " ORDER BY visited_at DESC LIMIT :limit";
        return $this->db->fetchMany($sql, ["limit" => $limit]);
    }
}