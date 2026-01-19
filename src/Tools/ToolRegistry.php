<?php

declare(strict_types=1);

namespace CleverBot\Tools;

use CleverBot\Tools\Exceptions\ToolNotFoundException;

/**
 * Registry for managing available tools
 */
class ToolRegistry
{
    /** @var array<string, ToolInterface> */
    private array $tools = [];

    /**
     * Register a tool
     */
    public function register(ToolInterface $tool): self
    {
        $this->tools[$tool->getName()] = $tool;
        return $this;
    }

    /**
     * Get tool definitions in format suitable for LLM APIs
     *
     * @return array<array<string, mixed>>
     */
    public function getDefinitions(): array
    {
        $definitions = [];
        
        foreach ($this->tools as $tool) {
            if ($tool instanceof Tool) {
                $definitions[] = $tool->getDefinition();
            } else {
                // Fallback for tools not extending Tool class
                $definitions[] = [
                    'type' => 'function',
                    'function' => [
                        'name' => $tool->getName(),
                        'description' => $tool->getDescription(),
                        'parameters' => $tool->getParameters(),
                    ],
                ];
            }
        }

        return $definitions;
    }

    /**
     * Execute a tool by name with the given arguments
     *
     * @param array<string, mixed> $arguments
     * @throws ToolNotFoundException
     */
    public function execute(string $name, array $arguments): mixed
    {
        if (!isset($this->tools[$name])) {
            throw new ToolNotFoundException($name);
        }

        return $this->tools[$name]->execute($arguments);
    }

    /**
     * Check if a tool exists
     */
    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    /**
     * Get all registered tools
     *
     * @return array<string, ToolInterface>
     */
    public function getTools(): array
    {
        return $this->tools;
    }
}
