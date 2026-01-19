<?php

declare(strict_types=1);

namespace CleverBot\Tests\Feature;

use CleverBot\AgentFactory;
use CleverBot\Facades\CleverBot;
use CleverBot\Tests\TestCase;

/**
 * Test the CleverBot Facade
 */
class FacadeTest extends TestCase
{
    public function test_facade_resolves_to_agent_factory(): void
    {
        $resolved = CleverBot::getFacadeRoot();

        $this->assertInstanceOf(AgentFactory::class, $resolved);
    }

    public function test_facade_can_access_with_tools_method(): void
    {
        $this->assertTrue(method_exists(CleverBot::class, 'withTools'));
    }

    public function test_facade_can_access_with_model_method(): void
    {
        $this->assertTrue(method_exists(CleverBot::class, 'withModel'));
    }

    public function test_facade_can_access_ask_method(): void
    {
        $this->assertTrue(method_exists(CleverBot::class, 'ask'));
    }
}
