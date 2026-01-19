<?php

declare(strict_types=1);

namespace CleverBot\Tests;

use CleverBot\CleverBotServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base test case for Clever Bot tests
 */
abstract class TestCase extends Orchestra
{

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        // Load .env file for testing
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    [$key, $value] = explode('=', $line, 2);
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
        
        parent::setUp();
    }

    /**
     * Get package providers
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            CleverBotServiceProvider::class,
        ];
    }

    /**
     * Get package aliases
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'CleverBot' => \CleverBot\Facades\CleverBot::class,
        ];
    }

    /**
     * Define environment setup
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Load environment variables from .env file
        $app->loadEnvironmentFrom('.env');
        
        // Setup default configuration values
        $app['config']->set('clever-bot.default_provider', 'gemini');
        $app['config']->set('clever-bot.providers.gemini.api_key', 'test-key');
        $app['config']->set('clever-bot.providers.gemini.model', 'gemini-2.5-flash');
        $app['config']->set('clever-bot.limits.max_messages', 50);
        $app['config']->set('clever-bot.limits.max_tokens', 4000);
        $app['config']->set('clever-bot.logging.enabled', false);
        $app['config']->set('clever-bot.cache.enabled', false);

        // Setup database for testing
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
