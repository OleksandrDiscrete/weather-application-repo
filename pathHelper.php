<?php
class PathHelper
{
    public static string $applicationPath = "http://localhost/weather-application-repo/";

    public static function getBasePath(): string
    {
        return self::$applicationPath;
    }

    public static function getAbsolutePath(string $relativePath): string
    {
        return self::getBasePath() . $relativePath;
    }
}