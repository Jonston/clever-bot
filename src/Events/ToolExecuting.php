<?php

declare(strict_types=1);

namespace CleverBot\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched before a tool is executed
 */
class ToolExecuting
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $toolName Name of the tool
     * @param array<string, mixed> $arguments Arguments passed to the tool
     * @param \DateTimeInterface $timestamp Timestamp of the event
     */
    public function __construct(
        public readonly string $toolName,
        public readonly array $arguments,
        public readonly \DateTimeInterface $timestamp
    ) {
    }
}
