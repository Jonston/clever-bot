<?php

declare(strict_types=1);

namespace CleverBot\Console\Commands;

use CleverBot\Exceptions\ConfigurationException;
use CleverBot\Models\AnthropicModel;
use CleverBot\Models\GeminiModel;
use CleverBot\Models\ModelInterface;
use CleverBot\Models\OpenAIModel;
use Illuminate\Console\Command;

/**
 * Command to test API connection for configured provider
 */
class TestConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clever-bot:test {provider?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test API connection for configured provider';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $provider = $this->argument('provider') ?? config('clever-bot.default_provider');

        $this->info("Testing {$provider} connection...");
        $this->newLine();

        try {
            $model = $this->getModel($provider);
            
            $this->line('Sending test request...');
            
            $response = $model->generate([
                ['role' => 'user', 'content' => 'Say "OK" if you can read this']
            ]);

            $this->newLine();
            $this->info('✓ Connection successful!');
            $this->line("Response: " . $response->getContent());
            $this->newLine();

            return self::SUCCESS;
            
        } catch (ConfigurationException $e) {
            $this->newLine();
            $this->error('✗ Configuration error!');
            $this->error($e->getMessage());
            $this->newLine();
            
            return self::FAILURE;
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Connection failed!');
            $this->error($e->getMessage());
            $this->newLine();
            
            return self::FAILURE;
        }
    }

    /**
     * Get model instance for the given provider
     *
     * @param string $provider Provider name
     * @return ModelInterface Model instance
     * @throws ConfigurationException
     */
    private function getModel(string $provider): ModelInterface
    {
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
    }
}
