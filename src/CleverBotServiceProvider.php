<?php

declare(strict_types=1);

namespace CleverBot;

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Console\Commands\InstallCommand;
use CleverBot\Console\Commands\TestConnectionCommand;
use CleverBot\Exceptions\ConfigurationException;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\AnthropicModel;
use CleverBot\Models\GeminiModel;
use CleverBot\Models\ModelInterface;
use CleverBot\Models\OpenAIModel;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\ToolRegistryBuilder;
use Illuminate\Support\ServiceProvider;

/**
 * Clever Bot Laravel Service Provider
 */
class CleverBotServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__.'/../config/clever-bot.php' => config_path('clever-bot.php'),
        ], 'clever-bot-config');

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/clever-bot.php', 'clever-bot'
        );

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                TestConnectionCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register ToolRegistryBuilder as singleton
        $this->app->singleton(ToolRegistryBuilder::class, function ($app) {
            return new ToolRegistryBuilder($app);
        });

        // Register ToolRegistry - created through builder from config
        $this->app->singleton(ToolRegistry::class, function ($app) {
            return $app->make(ToolRegistryBuilder::class)
                       ->buildFromConfig(); // Loads tools from config
        });

        // Register ModelInterface with default provider from config
        $this->app->bind(ModelInterface::class, function ($app) {
            $provider = config('clever-bot.default_provider', 'openai');
            $config = config("clever-bot.providers.{$provider}");

            if (!$config) {
                throw ConfigurationException::unknownProvider($provider);
            }

            if (empty($config['api_key'])) {
                throw ConfigurationException::missingApiKey($provider);
            }

            return match($provider) {
                'openai' => new OpenAIModel(
                    $config['api_key'],
                    $config['model'] ?? 'gpt-4',
                    [
                        'temperature' => $config['temperature'] ?? 0.7,
                        'max_tokens' => $config['max_tokens'] ?? 4000,
                    ]
                ),
                'anthropic' => new AnthropicModel(
                    $config['api_key'],
                    $config['model'] ?? 'claude-3-opus-20240229',
                    [
                        'temperature' => $config['temperature'] ?? 0.7,
                        'max_tokens' => $config['max_tokens'] ?? 4000,
                    ]
                ),
                'gemini' => new GeminiModel(
                    $config['api_key'],
                    $config['model'] ?? 'gemini-2.5-flash',
                    [
                        'temperature' => $config['temperature'] ?? 0.7,
                        'max_tokens' => $config['max_tokens'] ?? 4000,
                    ]
                ),
                default => throw ConfigurationException::unknownProvider($provider)
            };
        });

        // Register Agent
        $this->app->bind(Agent::class, function ($app) {
            return new Agent(
                name: 'default',
                model: $app->make(ModelInterface::class),
                toolRegistry: $app->make(ToolRegistry::class), // Now with tools from config
                messageManager: new MessageManager(
                    config('clever-bot.limits.max_messages', 50),
                    config('clever-bot.limits.max_tokens')
                ),
                config: new AgentConfig()
            );
        });

        // Register AgentFactory as singleton
        $this->app->singleton(AgentFactory::class, function ($app) {
            return new AgentFactory(
                $app->make(ToolRegistry::class),
                $app
            );
        });

        // Register facade accessor
        $this->app->singleton('clever-bot', function ($app) {
            return $app->make(AgentFactory::class);
        });
    }
}
