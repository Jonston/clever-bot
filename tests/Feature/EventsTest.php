<?php

declare(strict_types=1);

namespace CleverBot\Tests\Feature;

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Events\AgentCompleted;
use CleverBot\Events\AgentFailed;
use CleverBot\Events\AgentResponding;
use CleverBot\Events\AgentStarted;
use CleverBot\Events\AgentThinking;
use CleverBot\Events\ToolExecuted;
use CleverBot\Events\ToolExecuting;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\GeminiModel;
use CleverBot\Tests\TestCase;
use CleverBot\Tools\ToolRegistry;
use Illuminate\Support\Facades\Event;

/**
 * Test that events are dispatched correctly
 */
class EventsTest extends TestCase
{
    public function test_agent_started_event_is_dispatched(): void
    {
        Event::fake([AgentStarted::class]);

        $agent = $this->createTestAgent();
        
        try {
            $agent->execute('Hello');
        } catch (\Exception $e) {
            // Ignore execution errors, we're only testing event dispatch
        }

        Event::assertDispatched(AgentStarted::class, function ($event) {
            return $event->agentName === 'test-agent';
        });
    }

    public function test_agent_thinking_event_is_dispatched(): void
    {
        Event::fake([AgentThinking::class]);

        $agent = $this->createTestAgent();
        
        try {
            $agent->execute('Hello');
        } catch (\Exception $e) {
            // Ignore execution errors
        }

        Event::assertDispatched(AgentThinking::class, function ($event) {
            return $event->agentName === 'test-agent' && $event->messagesCount > 0;
        });
    }

    public function test_agent_responding_event_is_dispatched(): void
    {
        Event::fake([AgentResponding::class]);

        $agent = $this->createTestAgent();
        
        try {
            $agent->execute('Hello');
        } catch (\Exception $e) {
            // Ignore execution errors
        }

        Event::assertDispatched(AgentResponding::class, function ($event) {
            return $event->agentName === 'test-agent';
        });
    }

    public function test_agent_completed_event_is_dispatched(): void
    {
        Event::fake([AgentCompleted::class]);

        $agent = $this->createTestAgent();
        
        try {
            $agent->execute('Hello');
        } catch (\Exception $e) {
            // Ignore execution errors
        }

        Event::assertDispatched(AgentCompleted::class, function ($event) {
            return $event->agentName === 'test-agent' 
                && $event->totalTime >= 0
                && $event->toolsExecuted >= 0;
        });
    }

    public function test_agent_failed_event_is_dispatched_on_error(): void
    {
        Event::fake([AgentFailed::class]);

        // Create an agent with a model that will fail
        $model = new GeminiModel('invalid-key', 'gemini-2.5-flash');
        $agent = new Agent(
            name: 'failing-agent',
            model: $model,
            toolRegistry: new ToolRegistry(),
            messageManager: new MessageManager(50),
            config: new AgentConfig([])
        );

        try {
            $agent->execute('Hello');
        } catch (\Exception $e) {
            // Expected to fail
        }

        Event::assertDispatched(AgentFailed::class, function ($event) {
            return $event->agentName === 'failing-agent' 
                && $event->exception instanceof \Throwable;
        });
    }

    private function createTestAgent(): Agent
    {
        $model = new GeminiModel('test-key', 'gemini-2.5-flash');
        
        return new Agent(
            name: 'test-agent',
            model: $model,
            toolRegistry: new ToolRegistry(),
            messageManager: new MessageManager(50),
            config: new AgentConfig([])
        );
    }
}
