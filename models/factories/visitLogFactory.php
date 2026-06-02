<?php
namespace WeatherMaster\Models\Factories;

include_once __DIR__ . "/../visitLog.php";
use WeatherMaster\Models\VisitLog;
class VisitLogFactory
{
    public static function instantiate(): VisitLog
    {
        return new VisitLog(
            page: $_SERVER['REQUEST_URI'] ?? '/',
            ipAddress: $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            userAgent: substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        );
    }
}