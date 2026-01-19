<?php

declare(strict_types=1);

namespace CleverBot\Events;

use CleverBot\Messages\Message;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an agent starts executing
 */
class AgentStarted
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $agentName Name of the agent
     * @param string|Message $input User input
     * @param \DateTimeInterface $timestamp Timestamp of the event
     */
    public function __construct(
        public readonly string $agentName,
        public readonly string|Message $input,
        public readonly \DateTimeInterface $timestamp
    ) {
    }
}
