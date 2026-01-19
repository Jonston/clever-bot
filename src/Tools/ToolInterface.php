<?php

declare(strict_types=1);

namespace CleverBot\Tools;

/**
 * Interface for all tools that can be used by the agent
 */
interface ToolInterface
{
    /**
     * Get the unique name of the tool
     */
    public function getName(): string;

    /**
     * Get the description of what the tool does
     */
    public function getDescription(): string;

    /**
     * Get the parameters schema for the tool
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array;

    /**
     * Execute the tool with the given arguments
     *
     * @param array<string, mixed> $arguments
     * @return mixed
     */
    public function execute(array $arguments): mixed;
}
