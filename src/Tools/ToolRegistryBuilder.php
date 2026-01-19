<?php

declare(strict_types=1);

namespace CleverBot\Tools;

use Illuminate\Contracts\Container\Container;

/**
 * Builder for creating ToolRegistry from configuration
 */
class ToolRegistryBuilder
{
    /**
     * @param Container $container Laravel container for dependency resolution
     */
    public function __construct(
        private readonly Container $container
    ) {}

    /**
     * Build ToolRegistry from configuration
     *
     * @param string|null $preset Optional preset name from tool_presets config
     * @return ToolRegistry
     */
    public function buildFromConfig(?string $preset = null): ToolRegistry
    {
        $toolRegistry = new ToolRegistry();

        // Determine which tools to load
        $tools = $this->getToolsFromConfig($preset);

        // Register each tool
        foreach ($tools as $key => $value) {
            // Check if this is a parameterized tool (associative array entry)
            if (is_string($key) && is_array($value)) {
                // Format: ['ToolClass' => ['param' => 'value']]
                $toolClass = $key;
                $params = $value;
                $tool = $this->createToolWithParams($toolClass, $params);
            } else {
                // Format: ['ToolClass']
                $toolClass = $value;
                $tool = $this->createTool($toolClass);
            }

            $toolRegistry->register($tool);
        }

        return $toolRegistry;
    }

    /**
     * Get tools array from configuration
     *
     * @param string|null $preset Preset name or null for default tools
     * @return array<int|string, mixed>
     */
    private function getToolsFromConfig(?string $preset): array
    {
        if ($preset !== null) {
            // Load from preset
            $tools = config("clever-bot.tool_presets.{$preset}", []);
        } else {
            // Load default tools
            $tools = config('clever-bot.tools', []);
        }

        return is_array($tools) ? $tools : [];
    }

    /**
     * Create tool instance without parameters
     *
     * @param string $toolClass Tool class name
     * @return ToolInterface
     */
    private function createTool(string $toolClass): ToolInterface
    {
        return $this->container->make($toolClass);
    }

    /**
     * Create tool instance with constructor parameters
     *
     * @param string $toolClass Tool class name
     * @param array<string, mixed> $params Constructor parameters
     * @return ToolInterface
     */
    private function createToolWithParams(string $toolClass, array $params): ToolInterface
    {
        return $this->container->make($toolClass, $params);
    }
}
