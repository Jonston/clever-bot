<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use CleverBot\Config\EnvLoader;
use CleverBot\Models\AnthropicModel;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\Examples\GetWeatherTool;

// Load environment variables
EnvLoader::load();

echo "=== Anthropic Model Test ===\n\n";

// Get API key from environment
$apiKey = EnvLoader::get('ANTHROPIC_API_KEY', 'test-key');
$testMode = EnvLoader::get('TEST_MODE', 'true') === 'true';

echo "API Key: " . (str_starts_with($apiKey, 'sk-ant-') ? 'REAL KEY' : 'TEST KEY') . "\n";
echo "Test Mode: " . ($testMode ? 'ON' : 'OFF') . "\n\n";

// Initialize model
$anthropicModel = new AnthropicModel(apiKey: $apiKey, model: 'claude-3-sonnet-20240229');

echo "Testing basic text generation:\n";
try {
    $messages = [['role' => 'user', 'content' => 'Hello! What is 2+2? Keep your answer very short.']];
    $response = $anthropicModel->generate($messages);

    echo "✓ Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "Testing tool calling:\n";
try {
    $toolRegistry = new ToolRegistry();
    $weatherTool = new \CleverBot\Tools\Examples\GetWeatherTool();
    $toolRegistry->register($weatherTool);

    $messages = [['role' => 'user', 'content' => 'What is the weather like in Tokyo?']];
    $response = $anthropicModel->generate($messages, $toolRegistry->getDefinitions());

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

echo "Testing system message handling:\n";
try {
    $messages = [
        ['role' => 'system', 'content' => 'You are a helpful assistant that always responds in French.'],
        ['role' => 'user', 'content' => 'Hello! How are you?']
    ];
    $response = $anthropicModel->generate($messages);

    echo "✓ Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Anthropic Test Complete ===\n";