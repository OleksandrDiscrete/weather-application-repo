<?php
namespace WeatherMaster\Services;
class RegexService
{
    public static function validateEmail(string $email): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
    }

    public static function textToHtml(string $text): string
    {
        $html = preg_replace('/\*(.*?)\*/', '<strong>$1</strong>', $text); // *жирний* перетворює на <strong>
        $html = preg_replace('/\_(.*?)\_/', '<em>$1</em>', $html);         // _курсив_ перетворює на <em>
        return preg_replace('/\n/', '<br>', $html);                        // нові рядки перетворює на <br>
    }

    public static function validateUrl(string $url): bool
    {
        return (bool) preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $url);
    }

    public static function validateUaPhone(string $phone): bool
    {
        return (bool) preg_match('/^(\+38)?0\d{9}$/', $phone);
    }

    public static function getDayOfWeek(int $day, int $month, int $year): string
    {
        $dateStr = sprintf("%02d.%02d.%04d", $day, $month, $year);
        if (preg_match('/^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[012])\.(19|20)\d\d$/', $dateStr)) {
            $days = ["Неділя", "Понеділок", "Вівторок", "Середа", "Четвер", "П'ятниця", "Субота"];
            return $days[date("w", strtotime("$year-$month-$day"))];
        }
        return "Некоректний формат";
    }

    public static function replaceSpacesInFileName(string $filename): string
    {
        return preg_replace('/\s+/', '_', $filename);
    }

    public static function calculateDistance(string $coord1, string $coord2): ?float
    {
        $pattern = '/^(-?\d+(\.\d+)?)\s*,\s*(-?\d+(\.\d+)?)$/';
        if (preg_match($pattern, $coord1, $m1) && preg_match($pattern, $coord2, $m2)) {
            $lat1 = deg2rad((float) $m1[1]);
            $lon1 = deg2rad((float) $m1[3]);
            $lat2 = deg2rad((float) $m2[1]);
            $lon2 = deg2rad((float) $m2[3]);

            $dLat = $lat2 - $lat1;
            $dLon = $lon2 - $lon1;

            $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
            return round(6371 * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2); // Радіус Землі 6371 км
        }
        return null;
    }
}