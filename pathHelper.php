<?php
class PathHelper
{
    public static string $APPLICATION_PATH = "http://localhost/weather-application-repo/";
    public static function get_base_path(): string
    {
        return self::$APPLICATION_PATH;
    }
    public static function get_absolute_path(string $relative_path): string
    {
        return self::get_base_path() . $relative_path;
    }
}