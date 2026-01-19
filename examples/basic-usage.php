<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\OpenAIModel;
use CleverBot\Tools\ToolRegistry;

/**
 * Basic usage example of the CleverBot agent
 * 
 * This example demonstrates:
 * - Creating an agent with OpenAI model
 * - Simple text conversation without tools
 */

echo "=== CleverBot Basic Usage Example ===\n\n";

// Step 1: Create the model
$model = new OpenAIModel(
    apiKey: 'your-openai-api-key-here',
    model: 'gpt-4'
);

// Step 2: Create message manager with optional limits
$messageManager = new MessageManager(
    maxMessages: 20  // Keep last 20 messages
);

// Step 3: Create tool registry (empty for basic usage)
$toolRegistry = new ToolRegistry();

// Step 4: Create agent configuration
$config = new AgentConfig(
    maxIterations: 10,
    verbose: true
);

// Step 5: Create the agent
$agent = new Agent(
    name: 'BasicAssistant',
    model: $model,
    toolRegistry: $toolRegistry,
    messageManager: $messageManager,
    config: $config
);

// Step 6: Execute agent with user input
echo "User: Hello! Can you introduce yourself?\n\n";

$response = $agent->execute("Hello! Can you introduce yourself?");

echo "Assistant: {$response->getContent()}\n\n";

// Show metadata
echo "--- Response Metadata ---\n";
echo "Iterations: {$response->getIterations()}\n";
echo "Tool calls made: " . count($response->getToolCalls()) . "\n";

echo "\n=== Example Complete ===\n";
