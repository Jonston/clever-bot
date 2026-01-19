<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use CleverBot\Config\EnvLoader;
use CleverBot\Models\GeminiModel;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Messages\MessageManager;

// Load environment variables
EnvLoader::load();

echo "=== Gemini Model Test ===\n\n";

// Get API key from environment
$apiKey = EnvLoader::get('GEMINI_API_KEY', 'test-key');
$testMode = EnvLoader::get('TEST_MODE', 'true') === 'true';

echo "API Key: " . (str_starts_with($apiKey, 'AIza') ? 'REAL KEY' : 'TEST KEY') . "\n";
echo "Test Mode: " . ($testMode ? 'ON' : 'OFF') . "\n\n";

// Initialize model
$geminiModel = new GeminiModel(apiKey: $apiKey, model: 'gemini-2.5-flash');

echo "Testing basic text generation:\n";
echo "  → Preparing messages...\n";
try {
    $messages = [['role' => 'user', 'content' => 'Hello! What is 2+2? Keep your answer very short.']];
    echo "  → Sending request to Gemini API...\n";
    $response = $geminiModel->generate($messages);
    echo "  → Received response\n";

    echo "✓ Response: " . ($response->getContent() ?: 'No content') . "\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "Testing sequential tool calling with Agent:\n";
echo "  → Creating Agent with Gemini model and tools...\n";
try {
    $toolRegistry = new ToolRegistry();
    $listTool = new \CleverBot\Tools\Examples\ListProductsTool();
    $updateTool = new \CleverBot\Tools\Examples\UpdateProductTool();
    $toolRegistry->register($listTool);
    $toolRegistry->register($updateTool);
    echo "  → Tools registered: list_products, update_product\n";

    $messageManager = new MessageManager();
    $messageManager->addSystemMessage('You are an AI assistant with access to tools. Use the available tools to complete user requests. When you need information or to perform actions, call the appropriate tools.');
    $agentConfig = new AgentConfig(verbose: true, maxIterations: 5); // Verbose for output, limit iterations
    $agent = new Agent('TestAgent', $geminiModel, $toolRegistry, $messageManager, $agentConfig);

    $userInput = 'Use the list_products tool to get all products, then use update_product to add a 10% discount to smartphones costing more than 1000 dollars.';
    echo "  → User input: {$userInput}\n";
    echo "  → Executing Agent...\n";

    $response = $agent->execute($userInput);

    echo "  → Agent response: {$response->content}\n";
    echo "  → Metadata: " . json_encode($response->metadata, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Gemini Test Complete ===\n";