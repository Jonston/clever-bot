<?php

declare(strict_types=1);

namespace CleverBot\Facades;

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static AgentResponse ask(string $question)
 * @method static Agent withTools(array $tools)
 * @method static Agent withModel(string $provider, ?string $model = null)
 * 
 * @see \CleverBot\AgentFactory
 */
class CleverBot extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'clever-bot';
    }
}
