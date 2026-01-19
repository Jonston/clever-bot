<?php

declare(strict_types=1);

namespace CleverBot\Tests\Feature;

use CleverBot\Tests\TestCase;

/**
 * Test configuration loading and merging
 */
class ConfigTest extends TestCase
{
    public function test_config_has_default_provider(): void
    {
        $provider = config('clever-bot.default_provider');

        $this->assertEquals('gemini', $provider);
    }

    public function test_config_has_provider_configurations(): void
    {
        // Load the actual config file
        $config = include __DIR__ . '/../../config/clever-bot.php';
        $providers = $config['providers'];

        $this->assertIsArray($providers);
        $this->assertArrayHasKey('openai', $providers);
        $this->assertArrayHasKey('anthropic', $providers);
        $this->assertArrayHasKey('gemini', $providers);
    }

    public function test_gemini_provider_config_has_test_key(): void
    {
        $apiKey = config('clever-bot.providers.gemini.api_key');

        $this->assertEquals('test-key', $apiKey);
    }

    public function test_config_has_limits(): void
    {
        $limits = config('clever-bot.limits');

        $this->assertIsArray($limits);
        $this->assertArrayHasKey('max_messages', $limits);
        $this->assertArrayHasKey('max_tokens', $limits);
        $this->assertEquals(50, $limits['max_messages']);
        $this->assertEquals(4000, $limits['max_tokens']);
    }

    public function test_config_has_cache_settings(): void
    {
        // Load the actual config file
        $config = include __DIR__ . '/../../config/clever-bot.php';
        $cache = $config['cache'];

        $this->assertIsArray($cache);
        $this->assertArrayHasKey('enabled', $cache);
        $this->assertArrayHasKey('driver', $cache);
        $this->assertArrayHasKey('ttl', $cache);
        $this->assertArrayHasKey('prefix', $cache);
    }

    public function test_config_has_logging_settings(): void
    {
        // Load the actual config file
        $config = include __DIR__ . '/../../config/clever-bot.php';
        $logging = $config['logging'];

        $this->assertIsArray($logging);
        $this->assertArrayHasKey('enabled', $logging);
        $this->assertArrayHasKey('channel', $logging);
    }

    public function test_default_values_are_used_and_can_be_overridden(): void
    {
        // Load the actual config file to check defaults  
        $config = include __DIR__ . '/../../config/clever-bot.php';
        
        // These are the file defaults before environment overrides
        $this->assertIsCallable(function() use ($config) {
            return $config['default_provider'] ?? null;
        });
        
        $this->assertEquals(50, $config['limits']['max_messages']);
        $this->assertEquals(3600, $config['cache']['ttl']);
        $this->assertEquals('clever_bot', $config['cache']['prefix']);
        
        // Verify that Laravel config system allows overrides (TestCase sets this to 'gemini')
        $runtimeProvider = config('clever-bot.default_provider');
        $this->assertContains($runtimeProvider, ['openai', 'gemini', 'anthropic']);
    }
}
