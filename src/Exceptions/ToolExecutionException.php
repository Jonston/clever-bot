<?php

declare(strict_types=1);

namespace CleverBot\Exceptions;

/**
 * Exception thrown when tool execution fails
 */
class ToolExecutionException extends CleverBotException
{
    /**
     * @param string $message Error message
     * @param string $toolName Name of the tool that failed
     * @param array<string, mixed> $arguments Arguments passed to the tool
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        public readonly string $toolName,
        public readonly array $arguments,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
