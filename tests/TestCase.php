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
        // Setup default configuration values
        $app['config']->set('clever-bot.default_provider', 'gemini');
        $app['config']->set('clever-bot.providers.gemini.api_key', 'test-key');
        $app['config']->set('clever-bot.providers.gemini.model', 'gemini-2.5-flash');
        $app['config']->set('clever-bot.limits.max_messages', 50);
        $app['config']->set('clever-bot.limits.max_tokens', 4000);
        $app['config']->set('clever-bot.logging.enabled', false);
        $app['config']->set('clever-bot.cache.enabled', false);
    }
}
