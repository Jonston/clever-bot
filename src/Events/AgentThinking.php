<?php

declare(strict_types=1);

namespace CleverBot\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an agent is thinking (before model generation)
 */
class AgentThinking
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $agentName Name of the agent
     * @param int $messagesCount Number of messages in the conversation
     * @param bool $hasTools Whether tools are available
     */
    public function __construct(
        public readonly string $agentName,
        public readonly int $messagesCount,
        public readonly bool $hasTools
    ) {
    }
}
