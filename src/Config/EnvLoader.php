<?php

declare(strict_types=1);

namespace CleverBot\Config;

/**
 * Simple .env file loader for CleverBot
 */
class EnvLoader
{
    /**
     * Load environment variables from .env file
     */
    public static function load(string $path = null): void
    {
        $envFile = $path ?? dirname(__DIR__, 2) . '/.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                $value = trim($value, '"\'');

                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Get environment variable with optional default
     */
    public static function get(string $key, string $default = null): ?string
    {
        return getenv($key) ?: $default;
    }
}