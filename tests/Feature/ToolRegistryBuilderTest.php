<?php

declare(strict_types=1);

namespace CleverBot\Tests\Feature;

use CleverBot\Tests\TestCase;
use CleverBot\Tools\Tool;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\ToolRegistryBuilder;
use CleverBot\Tools\ToolResult;

/**
 * Mock tool for testing
 */
class MockSimpleTool extends Tool
{
    public function getName(): string
    {
        return 'mock_simple_tool';
    }

    public function getDescription(): string
    {
        return 'A simple mock tool for testing';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
        ];
    }

    public function execute(array $arguments): mixed
    {
        return new ToolResult(['success' => true]);
    }
}

/**
 * Mock tool with constructor parameters
 */
class MockParameterizedTool extends Tool
{
    public function __construct(
        private readonly string $connection = 'default',
        private readonly int $maxResults = 10
    ) {}

    public function getName(): string
    {
        return 'mock_parameterized_tool';
    }

    public function getDescription(): string
    {
        return 'A mock tool with parameters';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
        ];
    }

    public function execute(array $arguments): mixed
    {
        return new ToolResult([
            'connection' => $this->connection,
            'max_results' => $this->maxResults,
        ]);
    }

    public function getConnection(): string
    {
        return $this->connection;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }
}

/**
 * Test the ToolRegistryBuilder functionality
 */
class ToolRegistryBuilderTest extends TestCase
{
    public function test_builder_is_registered_as_singleton(): void
    {
        $builder1 = $this->app->make(ToolRegistryBuilder::class);
        $builder2 = $this->app->make(ToolRegistryBuilder::class);

        $this->assertInstanceOf(ToolRegistryBuilder::class, $builder1);
        $this->assertSame($builder1, $builder2, 'ToolRegistryBuilder should be a singleton');
    }

    public function test_build_from_empty_config_returns_empty_registry(): void
    {
        // Set empty tools config
        config(['clever-bot.tools' => []]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig();

        $this->assertInstanceOf(ToolRegistry::class, $registry);
        $this->assertEmpty($registry->getTools());
    }

    public function test_build_with_simple_tool_registration(): void
    {
        // Set config with simple tool
        config(['clever-bot.tools' => [
            MockSimpleTool::class,
        ]]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig();

        $this->assertCount(1, $registry->getTools());
        $this->assertTrue($registry->has('mock_simple_tool'));
    }

    public function test_build_with_multiple_simple_tools(): void
    {
        // Set config with multiple tools
        config(['clever-bot.tools' => [
            MockSimpleTool::class,
            MockParameterizedTool::class,
        ]]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig();

        $this->assertCount(2, $registry->getTools());
        $this->assertTrue($registry->has('mock_simple_tool'));
        $this->assertTrue($registry->has('mock_parameterized_tool'));
    }

    public function test_build_with_parameterized_tool(): void
    {
        // Set config with parameterized tool
        config(['clever-bot.tools' => [
            MockParameterizedTool::class => [
                'connection' => 'mysql',
                'maxResults' => 100,
            ],
        ]]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig();

        $this->assertCount(1, $registry->getTools());
        $this->assertTrue($registry->has('mock_parameterized_tool'));

        $tools = $registry->getTools();
        $tool = $tools['mock_parameterized_tool'];
        
        $this->assertInstanceOf(MockParameterizedTool::class, $tool);
        $this->assertEquals('mysql', $tool->getConnection());
        $this->assertEquals(100, $tool->getMaxResults());
    }

    public function test_build_with_mixed_tools(): void
    {
        // Set config with both simple and parameterized tools
        config(['clever-bot.tools' => [
            MockSimpleTool::class,
            MockParameterizedTool::class => [
                'connection' => 'postgres',
                'maxResults' => 50,
            ],
        ]]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig();

        $this->assertCount(2, $registry->getTools());
        
        // Verify simple tool
        $this->assertTrue($registry->has('mock_simple_tool'));
        
        // Verify parameterized tool with correct params
        $this->assertTrue($registry->has('mock_parameterized_tool'));
        $tools = $registry->getTools();
        $tool = $tools['mock_parameterized_tool'];
        $this->assertEquals('postgres', $tool->getConnection());
        $this->assertEquals(50, $tool->getMaxResults());
    }

    public function test_build_from_preset(): void
    {
        // Set up preset configuration
        config(['clever-bot.tool_presets' => [
            'test_preset' => [
                MockSimpleTool::class,
            ],
        ]]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig('test_preset');

        $this->assertCount(1, $registry->getTools());
        $this->assertTrue($registry->has('mock_simple_tool'));
    }

    public function test_build_from_preset_with_parameterized_tools(): void
    {
        // Set up preset with parameterized tools
        config(['clever-bot.tool_presets' => [
            'support' => [
                MockSimpleTool::class,
                MockParameterizedTool::class => [
                    'connection' => 'support_db',
                    'maxResults' => 25,
                ],
            ],
        ]]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig('support');

        $this->assertCount(2, $registry->getTools());
        
        $tools = $registry->getTools();
        $tool = $tools['mock_parameterized_tool'];
        $this->assertEquals('support_db', $tool->getConnection());
        $this->assertEquals(25, $tool->getMaxResults());
    }

    public function test_build_from_nonexistent_preset_returns_empty_registry(): void
    {
        // Don't set any presets
        config(['clever-bot.tool_presets' => []]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        $registry = $builder->buildFromConfig('nonexistent_preset');

        $this->assertInstanceOf(ToolRegistry::class, $registry);
        $this->assertEmpty($registry->getTools());
    }

    public function test_multiple_presets_are_independent(): void
    {
        // Set up multiple presets
        config(['clever-bot.tool_presets' => [
            'preset_a' => [
                MockSimpleTool::class,
            ],
            'preset_b' => [
                MockParameterizedTool::class => [
                    'connection' => 'preset_b_db',
                    'maxResults' => 99,
                ],
            ],
        ]]);

        $builder = $this->app->make(ToolRegistryBuilder::class);
        
        // Build from preset_a
        $registryA = $builder->buildFromConfig('preset_a');
        $this->assertCount(1, $registryA->getTools());
        $this->assertTrue($registryA->has('mock_simple_tool'));
        $this->assertFalse($registryA->has('mock_parameterized_tool'));
        
        // Build from preset_b
        $registryB = $builder->buildFromConfig('preset_b');
        $this->assertCount(1, $registryB->getTools());
        $this->assertFalse($registryB->has('mock_simple_tool'));
        $this->assertTrue($registryB->has('mock_parameterized_tool'));
    }

    public function test_default_registry_is_created_from_tools_config(): void
    {
        // Set default tools config
        config(['clever-bot.tools' => [
            MockSimpleTool::class,
        ]]);

        // Get the singleton registry that should be created by service provider
        $registry = $this->app->make(ToolRegistry::class);

        $this->assertInstanceOf(ToolRegistry::class, $registry);
        $this->assertTrue($registry->has('mock_simple_tool'));
    }

    public function test_config_has_tools_section(): void
    {
        // Load the actual config file
        $config = include __DIR__ . '/../../config/clever-bot.php';

        $this->assertArrayHasKey('tools', $config);
        $this->assertIsArray($config['tools']);
    }

    public function test_config_has_tool_presets_section(): void
    {
        // Load the actual config file
        $config = include __DIR__ . '/../../config/clever-bot.php';

        $this->assertArrayHasKey('tool_presets', $config);
        $this->assertIsArray($config['tool_presets']);
        
        // Verify preset keys exist
        $this->assertArrayHasKey('support', $config['tool_presets']);
        $this->assertArrayHasKey('sales', $config['tool_presets']);
        $this->assertArrayHasKey('admin', $config['tool_presets']);
    }
}
