<?php

declare(strict_types=1);

namespace CleverBot\Tests\Feature;

use CleverBot\Agent\Agent;
use CleverBot\AgentFactory;
use CleverBot\Models\ModelInterface;
use CleverBot\Tests\TestCase;
use CleverBot\Tools\ToolRegistry;

/**
 * Test the Service Provider bindings and registration
 */
class ServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_tool_registry_singleton(): void
    {
        $registry1 = $this->app->make(ToolRegistry::class);
        $registry2 = $this->app->make(ToolRegistry::class);

        $this->assertInstanceOf(ToolRegistry::class, $registry1);
        $this->assertSame($registry1, $registry2, 'ToolRegistry should be a singleton');
    }

    public function test_service_provider_registers_model_interface(): void
    {
        $model = $this->app->make(ModelInterface::class);

        $this->assertInstanceOf(ModelInterface::class, $model);
    }

    public function test_service_provider_registers_agent(): void
    {
        $agent = $this->app->make(Agent::class);

        $this->assertInstanceOf(Agent::class, $agent);
    }

    public function test_service_provider_registers_agent_factory(): void
    {
        $factory1 = $this->app->make(AgentFactory::class);
        $factory2 = $this->app->make(AgentFactory::class);

        $this->assertInstanceOf(AgentFactory::class, $factory1);
        $this->assertSame($factory1, $factory2, 'AgentFactory should be a singleton');
    }

    public function test_service_provider_registers_facade_accessor(): void
    {
        $cleverBot = $this->app->make('clever-bot');

        $this->assertInstanceOf(AgentFactory::class, $cleverBot);
    }

    public function test_commands_are_registered(): void
    {
        $commands = $this->app['artisan']->all();

        $this->assertArrayHasKey('clever-bot:install', $commands);
        $this->assertArrayHasKey('clever-bot:test', $commands);
    }

    public function test_config_is_published(): void
    {
        $this->assertFileExists(__DIR__ . '/../../config/clever-bot.php');
    }

    public function test_config_has_expected_keys(): void
    {
        $config = include __DIR__ . '/../../config/clever-bot.php';

        $this->assertArrayHasKey('default_provider', $config);
        $this->assertArrayHasKey('providers', $config);
        $this->assertArrayHasKey('limits', $config);
        $this->assertArrayHasKey('cache', $config);
        $this->assertArrayHasKey('logging', $config);
    }
}
