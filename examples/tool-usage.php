<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\AnthropicModel;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\Examples\GetWeatherTool;

/**
 * Tool usage example of the CleverBot agent
 * 
 * This example demonstrates:
 * - Creating an agent with Anthropic Claude model
 * - Registering tools
 * - Agent automatically calling tools based on user input
 * - Multi-turn conversation with tool execution
 */

echo "=== CleverBot Tool Usage Example ===\n\n";

// Step 1: Create the model (using Anthropic this time)
$model = new AnthropicModel(
    apiKey: 'your-anthropic-api-key-here',
    model: 'claude-3-sonnet-20240229'
);

// Step 2: Create message manager
$messageManager = new MessageManager(
    maxMessages: 50  // Keep last 50 messages for longer conversations
);

// Step 3: Create and configure tool registry
$toolRegistry = new ToolRegistry();

// Register the weather tool
$toolRegistry->register(new GetWeatherTool());

// You can register multiple tools:
// $toolRegistry->register(new CalculatorTool());
// $toolRegistry->register(new WebSearchTool());

// Step 4: Create agent configuration with verbose output
$config = new AgentConfig(
    maxIterations: 15,
    verbose: true  // Show tool execution details
);

// Step 5: Create the agent
$agent = new Agent(
    name: 'WeatherAssistant',
    model: $model,
    toolRegistry: $toolRegistry,
    messageManager: $messageManager,
    config: $config
);

// Step 6: Execute agent with a query that should trigger tool usage
echo "User: What's the weather like in San Francisco?\n\n";

$response = $agent->execute("What's the weather like in San Francisco?");

echo "\nAssistant: {$response->getContent()}\n\n";

// Show execution details
echo "--- Execution Details ---\n";
echo "Total iterations: {$response->getIterations()}\n";
echo "Tool calls made: " . count($response->getToolCalls()) . "\n\n";

if (!empty($response->getToolCalls())) {
    echo "Tool call history:\n";
    foreach ($response->getToolCalls() as $toolCall) {
        echo "  - {$toolCall['tool']} (iteration {$toolCall['iteration']})\n";
        echo "    Arguments: " . json_encode($toolCall['arguments']) . "\n";
    }
}

echo "\n=== Example Complete ===\n";

// Additional example: Multiple tool calls in conversation
echo "\n=== Multi-turn Conversation Example ===\n\n";

// Create a fresh agent for second example
$messageManager2 = new MessageManager();
$toolRegistry2 = new ToolRegistry();
$toolRegistry2->register(new GetWeatherTool());

$agent2 = new Agent(
    name: 'MultiTurnAssistant',
    model: $model,
    toolRegistry: $toolRegistry2,
    messageManager: $messageManager2,
    config: new AgentConfig(verbose: false)
);

// First turn
echo "User: What's the weather in London?\n";
$response1 = $agent2->execute("What's the weather in London?");
echo "Assistant: {$response1->getContent()}\n\n";

// Second turn - the agent remembers context
echo "User: How about in Tokyo?\n";
$response2 = $agent2->execute("How about in Tokyo?");
echo "Assistant: {$response2->getContent()}\n\n";

// Third turn
echo "User: Which city is warmer?\n";
$response3 = $agent2->execute("Which city is warmer?");
echo "Assistant: {$response3->getContent()}\n\n";

echo "=== Multi-turn Example Complete ===\n";
