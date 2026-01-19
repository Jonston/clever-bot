<?php

declare(strict_types=1);

namespace CleverBot;

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Agent\AgentResponse;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\AnthropicModel;
use CleverBot\Models\GeminiModel;
use CleverBot\Models\ModelInterface;
use CleverBot\Models\OpenAIModel;
use CleverBot\Tools\ToolRegistry;
use Illuminate\Contracts\Container\Container;

/**
 * Factory class for creating and configuring Clever Bot agents
 */
class AgentFactory
{
    /**
     * @param ToolRegistry $toolRegistry Default tool registry
     * @param Container $container Laravel container
     */
    public function __construct(
        private readonly ToolRegistry $toolRegistry,
        private readonly Container $container
    ) {
    }

    /**
     * Ask a question using the default agent configuration
     *
     * @param string $question Question to ask
     * @return AgentResponse Agent's response
     */
    public function ask(string $question): AgentResponse
    {
        $agent = $this->container->make(Agent::class);
        return $agent->execute($question);
    }

    /**
     * Create an agent with specific tools
     *
     * @param array<ToolInterface> $tools Tools to register
     * @return Agent Configured agent
     */
    public function withTools(array $tools): Agent
    {
        $registry = new ToolRegistry();
        
        foreach ($tools as $tool) {
            $registry->register($tool);
        }

        return new Agent(
            name: 'custom',
            model: $this->container->make(ModelInterface::class),
            toolRegistry: $registry,
            messageManager: new MessageManager(
                config('clever-bot.limits.max_messages', 50),
                config('clever-bot.limits.max_tokens')
            ),
            config: new AgentConfig([])
        );
    }

    /**
     * Create an agent with a specific model provider
     *
     * @param string $provider Provider name (openai, anthropic, gemini)
     * @param string|null $model Optional model name override
     * @return Agent Configured agent
     */
    public function withModel(string $provider, ?string $model = null): Agent
    {
        $config = config("clever-bot.providers.{$provider}");
        
        $modelInstance = match($provider) {
            'openai' => new OpenAIModel(
                $config['api_key'],
                $model ?? $config['model'],
                [
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_tokens' => $config['max_tokens'] ?? 4000,
                ]
            ),
            'anthropic' => new AnthropicModel(
                $config['api_key'],
                $model ?? $config['model'],
                [
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_tokens' => $config['max_tokens'] ?? 4000,
                ]
            ),
            'gemini' => new GeminiModel(
                $config['api_key'],
                $model ?? $config['model'],
                [
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_tokens' => $config['max_tokens'] ?? 4000,
                ]
            ),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}")
        };

        return new Agent(
            name: 'custom',
            model: $modelInstance,
            toolRegistry: $this->container->make(ToolRegistry::class),
            messageManager: new MessageManager(
                config('clever-bot.limits.max_messages', 50),
                config('clever-bot.limits.max_tokens')
            ),
            config: new AgentConfig([])
        );
    }
}
