<?php

declare(strict_types=1);

namespace CleverBot\Agent;

/**
 * Response from the agent after processing
 */
final readonly class AgentResponse
{
    /**
     * @param string $content Final response content
     * @param array<string, mixed> $metadata Response metadata (iterations, tool calls, etc.)
     */
    public function __construct(
        public string $content,
        public array $metadata = []
    ) {
    }

    /**
     * Get the content of the response
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get metadata
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get number of iterations used
     */
    public function getIterations(): int
    {
        return $this->metadata['iterations'] ?? 0;
    }

    /**
     * Get tool calls that were made
     *
     * @return array<string, mixed>
     */
    public function getToolCalls(): array
    {
        return $this->metadata['tool_calls'] ?? [];
    }
}
