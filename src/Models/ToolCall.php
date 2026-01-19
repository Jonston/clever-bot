<?php

declare(strict_types=1);

namespace CleverBot\Models;

/**
 * Value object representing a tool call from the model
 */
final readonly class ToolCall
{
    /**
     * @param string $id Unique identifier for the tool call
     * @param string $name Name of the tool to call
     * @param array<string, mixed> $arguments Arguments to pass to the tool
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $arguments
    ) {
    }

    /**
     * Convert to array format
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'arguments' => $this->arguments,
        ];
    }
}
