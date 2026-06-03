<?php

/**
 * Custom Autoloader for the WeatherMaster Project
 * Based on the PHP-FIG PSR-4 standard.
 */
spl_autoload_register(function (string $class) {
    $prefix = 'WeatherMaster\\';

    // 2. Define the base directory for that namespace (pointing to the root of your project)
    $base_dir = __DIR__ . '/../';

    // 3. Check if the requested class uses our namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // If it doesn't, ignore it and let other autoloaders (if any) handle it
        return;
    }

    // 4. Get the relative class name (e.g., "Controllers\HomeController")
    $relative_class = substr($class, $len);

    // 5. IMPORTANT: Handle folder casing. 
    // Your namespaces use uppercase (e.g., Controllers), but your actual folders 
    // are lowercase (e.g., controllers/). We need to lowercase the first segment.
    $parts = explode('\\', $relative_class);
    if (count($parts) > 1) {
        $parts[0] = strtolower($parts[0]); // Changes 'Controllers' to 'controllers'
    }

    $mapped_class = implode('/', $parts);

    // 6. Build the full path to the file
    $file = $base_dir . $mapped_class . '.php';

    // 7. If the file exists, require it!
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Helpful debugging for when you misspell a namespace or file name
        die("Autoloader Error: Cannot find file for class {$class} at {$file}");
    }
});