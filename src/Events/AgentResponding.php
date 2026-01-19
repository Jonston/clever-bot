<?php

declare(strict_types=1);

namespace CleverBot\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an agent is about to respond
 */
class AgentResponding
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $agentName Name of the agent
     * @param string|null $response Response content (preview)
     * @param \DateTimeInterface $timestamp Timestamp of the event
     */
    public function __construct(
        public readonly string $agentName,
        public readonly ?string $response,
        public readonly \DateTimeInterface $timestamp
    ) {
    }
}
