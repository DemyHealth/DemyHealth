<?php

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\' => __DIR__.'/app/',
        'Database\\Seeders\\' => __DIR__.'/database/seeders/',
        'Tests\\' => __DIR__.'/tests/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir.str_replace('\\', '/', $relative).'.php';
            if (is_file($file)) {
                require $file;
            }
        }
    }
});

if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        return $value === false ? $default : $value;
    }
}
