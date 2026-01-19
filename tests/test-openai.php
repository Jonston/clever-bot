<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use CleverBot\Config\EnvLoader;
use CleverBot\Models\OpenAIModel;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\Examples\GetWeatherTool;

// Load environment variables
EnvLoader::load();

echo "=== OpenAI Model Test ===\n\n";

// Get API key from environment
$apiKey = EnvLoader::get('OPENAI_API_KEY', 'test-key');
$testMode = EnvLoader::get('TEST_MODE', 'true') === 'true';

echo "API Key: " . (str_starts_with($apiKey, 'sk-') ? 'REAL KEY' : 'TEST KEY') . "\n";
echo "Test Mode: " . ($testMode ? 'ON' : 'OFF') . "\n\n";

// Initialize model
$openaiModel = new OpenAIModel(apiKey: $apiKey, model: 'gpt-4');

echo "Testing basic text generation:\n";
try {
    $messages = [['role' => 'user', 'content' => 'Hello! What is 2+2? Keep your answer very short.']];
    $response = $openaiModel->generate($messages);

    echo "✓ Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "Testing tool calling:\n";
try {
    $toolRegistry = new ToolRegistry();
    $weatherTool = new \CleverBot\Tools\Examples\GetWeatherTool();
    $toolRegistry->register($weatherTool);

    $messages = [['role' => 'user', 'content' => 'What is the weather like in Paris?']];
    $response = $openaiModel->generate($messages, $toolRegistry->getDefinitions());

    if ($response->hasToolCalls()) {
        echo "✓ Tool call detected: " . $response->getToolCalls()[0]->name . "\n";
        echo "  Arguments: " . json_encode($response->getToolCalls()[0]->arguments) . "\n";
    } else {
        echo "✓ Response: " . ($response->getContent() ?: 'No content') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "Testing multi-turn conversation:\n";
try {
    $messages = [
        ['role' => 'user', 'content' => 'My name is John.'],
        ['role' => 'assistant', 'content' => 'Hello John! Nice to meet you.'],
        ['role' => 'user', 'content' => 'What is my name?']
    ];
    $response = $openaiModel->generate($messages);

    echo "✓ Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== OpenAI Test Complete ===\n";