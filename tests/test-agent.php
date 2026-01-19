<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use CleverBot\Config\EnvLoader;
use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Models\OpenAIModel;
use CleverBot\Models\AnthropicModel;
use CleverBot\Models\GeminiModel;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\Examples\GetWeatherTool;
use CleverBot\Messages\MessageManager;

// Load environment variables
EnvLoader::load();

echo "=== Agent Integration Test ===\n\n";

// Get API keys from environment
$openaiKey = EnvLoader::get('OPENAI_API_KEY', 'test-key');
$anthropicKey = EnvLoader::get('ANTHROPIC_API_KEY', 'test-key');
$geminiKey = EnvLoader::get('GEMINI_API_KEY', 'test-key');
$testMode = EnvLoader::get('TEST_MODE', 'true') === 'true';

echo "API Keys Status:\n";
echo "- OpenAI: " . (str_starts_with($openaiKey, 'sk-') ? 'REAL' : 'TEST') . "\n";
echo "- Anthropic: " . (str_starts_with($anthropicKey, 'sk-ant-') ? 'REAL' : 'TEST') . "\n";
echo "- Gemini: " . (str_starts_with($geminiKey, 'AIza') ? 'REAL' : 'TEST') . "\n";
echo "Test Mode: " . ($testMode ? 'ON' : 'OFF') . "\n\n";

// Initialize models
$openaiModel = new OpenAIModel(apiKey: $openaiKey, model: 'gpt-4');
$anthropicModel = new AnthropicModel(apiKey: $anthropicKey, model: 'claude-3-sonnet-20240229');
$geminiModel = new GeminiModel(apiKey: $geminiKey, model: 'gemini-2.5-flash');

$models = [
    'OpenAI' => $openaiModel,
    'Anthropic' => $anthropicModel,
    'Gemini' => $geminiModel,
];

// Setup tools
$toolRegistry = new ToolRegistry();
$weatherTool = new \CleverBot\Tools\Examples\GetWeatherTool();
$toolRegistry->register($weatherTool);

$messageManager = new MessageManager(maxMessages: 20);

foreach ($models as $modelName => $model) {
    echo "Testing $modelName Agent:\n";

    try {
        $agent = new Agent(
            name: "{$modelName}Agent",
            model: $model,
            toolRegistry: $toolRegistry,
            messageManager: $messageManager,
            config: new AgentConfig(verbose: false)
        );

        $result = $agent->execute("What's the weather like in London and what should I wear?");

        echo "✓ Agent Response: " . substr($result->content, 0, 100) . "...\n";
        echo "✓ Messages in history: " . count($messageManager->getMessages()) . "\n\n";

    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "Testing model switching:\n";
try {
    // Test switching between models
    $agent = new Agent(
        name: 'MultiModelAgent',
        model: $openaiModel,
        toolRegistry: $toolRegistry,
        messageManager: new MessageManager(maxMessages: 10),
        config: new AgentConfig(verbose: false)
    );

    $response1 = $agent->execute("Hello from OpenAI!");
    echo "✓ OpenAI: " . substr($response1->content, 0, 50) . "...\n";

    // Switch to Anthropic
    $agent2 = new Agent(
        name: 'MultiModelAgent',
        model: $anthropicModel,
        toolRegistry: $toolRegistry,
        messageManager: new MessageManager(maxMessages: 10),
        config: new AgentConfig(verbose: false)
    );

    $response2 = $agent2->execute("Hello from Anthropic!");
    echo "✓ Anthropic: " . substr($response2->content, 0, 50) . "...\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Agent Integration Test Complete ===\n";