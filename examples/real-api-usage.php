<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Models\OpenAIModel;
use CleverBot\Models\AnthropicModel;
use CleverBot\Models\GeminiModel;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Messages\MessageManager;

echo "=== CleverBot Real API Usage Example ===\n\n";

// Note: This example requires real API keys to be set as environment variables
// Set them like:
// export OPENAI_API_KEY="your-openai-key"
// export ANTHROPIC_API_KEY="your-anthropic-key"
// export GEMINI_API_KEY="your-gemini-key"

$openaiKey = getenv('OPENAI_API_KEY') ?: 'test-key';
$anthropicKey = getenv('ANTHROPIC_API_KEY') ?: 'test-key';
$geminiKey = getenv('GEMINI_API_KEY') ?: 'test-key';

echo "Using API keys:\n";
echo "- OpenAI: " . (str_starts_with($openaiKey, 'sk-') ? 'REAL KEY' : 'TEST KEY') . "\n";
echo "- Anthropic: " . (str_starts_with($anthropicKey, 'sk-ant-') ? 'REAL KEY' : 'TEST KEY') . "\n";
echo "- Gemini: " . (str_starts_with($geminiKey, 'AIza') ? 'REAL KEY' : 'TEST KEY') . "\n\n";

// Initialize models
$openaiModel = new OpenAIModel(apiKey: $openaiKey, model: 'gpt-4');
$anthropicModel = new AnthropicModel(apiKey: $anthropicKey, model: 'claude-3-sonnet-20240229');
$geminiModel = new GeminiModel(apiKey: $geminiKey, model: 'gemini-2.5-flash');

// Setup tools
$toolRegistry = new ToolRegistry();
$weatherTool = new \CleverBot\Tools\Examples\GetWeatherTool();
$toolRegistry->register($weatherTool);

// Test each model
$testMessage = "Hello! Can you tell me what 2+2 equals?";

echo "Testing OpenAI Model:\n";
try {
    $response = $openaiModel->generate([['role' => 'user', 'content' => $testMessage]]);
    echo "Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "Testing Anthropic Model:\n";
try {
    $response = $anthropicModel->generate([['role' => 'user', 'content' => $testMessage]]);
    echo "Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "Testing Gemini Model:\n";
try {
    $response = $geminiModel->generate([['role' => 'user', 'content' => $testMessage]]);
    echo "Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test tool calling with weather
$weatherMessage = "What's the weather like in Paris?";

echo "Testing Tool Calling with OpenAI:\n";
try {
    $response = $openaiModel->generate(
        [['role' => 'user', 'content' => $weatherMessage]],
        $toolRegistry->getDefinitions()
    );
    if ($response->hasToolCalls()) {
        echo "Tool call detected: " . $response->getToolCalls()[0]->name . "\n";
    } else {
        echo "Response: " . ($response->getContent() ?: 'No content') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "Testing Tool Calling with Gemini:\n";
try {
    $response = $geminiModel->generate(
        [['role' => 'user', 'content' => $weatherMessage]],
        $toolRegistry->getDefinitions()
    );
    if ($response->hasToolCalls()) {
        echo "Tool call detected: " . $response->getToolCalls()[0]->name . "\n";
    } else {
        echo "Response: " . ($response->getContent() ?: 'No content') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test full agent with OpenAI
echo "Testing Full Agent with OpenAI:\n";
try {
    $messageManager = new MessageManager(maxMessages: 20);
    $agent = new Agent(
        name: 'WeatherAgent',
        model: $openaiModel,
        toolRegistry: $toolRegistry,
        messageManager: $messageManager,
        config: new AgentConfig(verbose: true)
    );

    $result = $agent->execute("What's the weather like in London?");
    echo "Final result: " . $result->content . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Example Complete ===\n";
echo "To use real APIs, set the environment variables with your actual API keys.\n";
echo "Test keys will use mock responses for verification.\n";