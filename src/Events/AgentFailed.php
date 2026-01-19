<?php

declare(strict_types=1);

namespace CleverBot\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an agent execution fails
 */
class AgentFailed
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $agentName Name of the agent
     * @param \Throwable $exception Exception that caused the failure
     * @param \DateTimeInterface $timestamp Timestamp of the event
     */
    public function __construct(
        public readonly string $agentName,
        public readonly \Throwable $exception,
        public readonly \DateTimeInterface $timestamp
    ) {
    }
}
