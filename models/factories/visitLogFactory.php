<?php
namespace WeatherMaster\Models\Factories;

include_once __DIR__ . "/../visitLog.php";
use WeatherMaster\Models\VisitLog;
class VisitLogFactory
{
    public static function instantiate(): VisitLog
    {
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $cleanPage = parse_url($rawUri, PHP_URL_PATH);

        if (!$cleanPage) {
            $cleanPage = '/';
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (str_contains($ip, ',')) {
            $ip = explode(',', $ip)[0];
        }

        return new VisitLog(
            page: $cleanPage,
            ipAddress: trim($ip),
            userAgent: substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        );
    }
}