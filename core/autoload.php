<?php

/**
 * Custom Autoloader for the WeatherMaster Project
 * Based on the PHP-FIG PSR-4 standard.
 */
spl_autoload_register(function (string $class) {
    $prefix = 'WeatherMaster\\';

    $base_dir = __DIR__ . '/../';


    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    if (count($parts) > 1) {
        $parts[0] = strtolower($parts[0]);
    }

    $mapped_class = implode('/', $parts);
    $file = $base_dir . $mapped_class . '.php';

    if (file_exists($file)) {
        require_once $file;
    } else {
        die("Autoloader Error: Cannot find file for class {$class} at {$file}");
    }
});