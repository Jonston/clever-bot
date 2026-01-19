<?php

declare(strict_types=1);

namespace CleverBot\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched after a tool is executed
 */
class ToolExecuted
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $toolName Name of the tool
     * @param mixed $result Result from the tool execution
     * @param float $executionTime Execution time in seconds
     */
    public function __construct(
        public readonly string $toolName,
        public readonly mixed $result,
        public readonly float $executionTime
    ) {
    }
}
