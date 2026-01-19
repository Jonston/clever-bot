<?php

declare(strict_types=1);

namespace CleverBot\Events;

use CleverBot\Agent\AgentResponse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an agent completes execution successfully
 */
class AgentCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * @param string $agentName Name of the agent
     * @param float $totalTime Total execution time in seconds
     * @param int $toolsExecuted Number of tools executed
     * @param AgentResponse $response Final response from the agent
     */
    public function __construct(
        public readonly string $agentName,
        public readonly float $totalTime,
        public readonly int $toolsExecuted,
        public readonly AgentResponse $response
    ) {
    }
}
