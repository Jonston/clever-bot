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
        $providers = config('clever-bot.providers');

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
        $cache = config('clever-bot.cache');

        $this->assertIsArray($cache);
        $this->assertArrayHasKey('enabled', $cache);
        $this->assertArrayHasKey('driver', $cache);
        $this->assertArrayHasKey('ttl', $cache);
        $this->assertArrayHasKey('prefix', $cache);
    }

    public function test_config_has_logging_settings(): void
    {
        $logging = config('clever-bot.logging');

        $this->assertIsArray($logging);
        $this->assertArrayHasKey('enabled', $logging);
        $this->assertArrayHasKey('channel', $logging);
    }

    public function test_default_values_are_used(): void
    {
        $this->assertEquals('openai', config('clever-bot.default_provider'));
        $this->assertEquals(50, config('clever-bot.limits.max_messages'));
        $this->assertEquals(3600, config('clever-bot.cache.ttl'));
        $this->assertEquals('clever_bot', config('clever-bot.cache.prefix'));
    }
}
