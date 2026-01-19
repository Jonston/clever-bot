<?php

declare(strict_types=1);

namespace CleverBot\Tools;

/**
 * Abstract base class for tools with common functionality
 */
abstract class Tool implements ToolInterface
{
    /**
     * Execute the tool and return a ToolResult
     *
     * @param array<string, mixed> $arguments
     */
    abstract public function execute(array $arguments): mixed;

    /**
     * Get the tool definition in a format suitable for LLM APIs
     *
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => $this->getParameters(),
            ],
        ];
    }
}
