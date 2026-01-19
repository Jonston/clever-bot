<?php

declare(strict_types=1);

namespace CleverBot\Agent;

/**
 * Configuration for the agent
 */
final readonly class AgentConfig
{
    /**
     * @param int $maxIterations Maximum number of iterations for tool calls (prevents infinite loops)
     * @param bool $verbose Whether to output verbose logging
     * @param array<string, mixed> $metadata Additional metadata
     */
    public function __construct(
        public int $maxIterations = 10,
        public bool $verbose = false,
        public array $metadata = []
    ) {
    }
}
