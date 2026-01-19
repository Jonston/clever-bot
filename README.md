# Clever Bot

Laravel AI Agent package with multi-model support and extensible tool system.

## Overview

Clever Bot is a flexible PHP library for building AI agents that can interact with multiple Large Language Models (LLMs) and execute tools/functions. It provides a clean abstraction layer over different AI providers (OpenAI, Anthropic, Gemini) and a robust system for tool integration.

## Features

- 🤖 **Multi-Model Support**: Seamlessly switch between OpenAI, Anthropic, and Gemini models
- 🛠️ **Extensible Tool System**: Easy-to-use tool/function calling interface
- 💬 **Message Management**: Built-in conversation history with configurable limits
- 🔄 **Recursive Execution**: Automatic handling of multi-turn tool interactions
- 🎯 **Type-Safe**: Full PHP 8.1+ type safety with strict typing
- 🏗️ **Clean Architecture**: Well-structured, PSR-4 compliant codebase
- 🌐 **Real API Integration**: Production-ready HTTP clients using Guzzle for all supported models

## Architecture

### Component Overview

| Component | Responsibility | Key Files |
|-----------|---------------|-----------|
| **Agent** | Main orchestrator that coordinates model, tools, and messages | `Agent.php`, `AgentConfig.php`, `AgentResponse.php` |
| **Models** | Abstraction layer for different LLM providers | `ModelInterface.php`, `OpenAIModel.php`, `AnthropicModel.php`, `GeminiModel.php` |
| **Tools** | System for registering and executing agent capabilities | `ToolRegistry.php`, `ToolInterface.php`, `Tool.php` |
| **Messages** | Conversation history management with limits | `MessageManager.php`, `Message.php` |

### Execution Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User Input                                               │
│    execute(string $input)                                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Add to Message History                                   │
│    MessageManager->addUserMessage()                         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Generate Model Response                                  │
│    Model->generate(messages, toolDefinitions)               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
              ┌──────┴──────┐
              │             │
              ▼             ▼
    ┌─────────────┐   ┌──────────────┐
    │ Has Tool    │   │ No Tool      │
    │ Calls?      │   │ Calls        │
    └──────┬──────┘   └──────┬───────┘
           │                 │
           ▼                 ▼
┌─────────────────────┐   ┌──────────────────┐
│ 4a. Execute Tools   │   │ 4b. Return Final │
│ handleToolCalls()   │   │ Response         │
└──────────┬──────────┘   └──────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. Add Tool Results to Messages                             │
│    MessageManager->addToolResults()                         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
           ┌─────────────────┐
           │ 6. Recurse back │
           │ to step 3       │
           └─────────────────┘
```

### Key Design Patterns

1. **Interface Segregation**: Each component has a clear interface (`ModelInterface`, `ToolInterface`)
2. **Strategy Pattern**: Different model implementations (OpenAI, Anthropic) implement the same interface
3. **Registry Pattern**: Tools are registered and managed centrally
4. **Value Objects**: Immutable objects for `Message`, `ToolCall`, `ModelResponse`, etc.
5. **Recursive Execution**: Agent automatically handles multi-turn tool calling scenarios

## Installation

```bash
composer require jonston/clever-bot
```

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x (optional, uses illuminate/support)

## Usage

### Basic Usage

```php
use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\OpenAIModel;
use CleverBot\Tools\ToolRegistry;

// Create the model
$model = new OpenAIModel(
    apiKey: 'your-openai-api-key',
    model: 'gpt-4'
);

// Create message manager
$messageManager = new MessageManager(maxMessages: 20);

// Create tool registry
$toolRegistry = new ToolRegistry();

// Create agent
$agent = new Agent(
    name: 'MyAssistant',
    model: $model,
    toolRegistry: $toolRegistry,
    messageManager: $messageManager,
    config: new AgentConfig(verbose: true)
);

// Execute
$response = $agent->execute("Hello! How can you help me?");
echo $response->getContent();
```

### Using Tools

```php
use CleverBot\Tools\Examples\GetWeatherTool;

// Register tools
$toolRegistry = new ToolRegistry();
$toolRegistry->register(new GetWeatherTool());

// Create agent with tools
$agent = new Agent(
    name: 'WeatherBot',
    model: $model,
    toolRegistry: $toolRegistry,
    messageManager: $messageManager
);

// Ask question that triggers tool usage
$response = $agent->execute("What's the weather in San Francisco?");

// Agent will automatically:
// 1. Recognize it needs to call get_weather tool
// 2. Execute the tool with appropriate parameters
// 3. Send results back to the model
// 4. Return final natural language response
```

### Creating Custom Tools

```php
use CleverBot\Tools\Tool;
use CleverBot\Tools\ToolResult;

class CalculatorTool extends Tool
{
    public function getName(): string
    {
        return 'calculator';
    }

    public function getDescription(): string
    {
        return 'Perform basic mathematical calculations';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'expression' => [
                    'type' => 'string',
                    'description' => 'Mathematical expression to evaluate',
                ],
            ],
            'required' => ['expression'],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $expression = $arguments['expression'];
        
        // Safe evaluation logic here
        $result = $this->evaluateExpression($expression);
        
        return ToolResult::success([
            'expression' => $expression,
            'result' => $result,
        ]);
    }
    
    private function evaluateExpression(string $expr): float
    {
        // Implementation here
        return 42.0;
    }
}
```

### Using Different Models

#### OpenAI

```php
$model = new OpenAIModel(
    apiKey: 'sk-...',
    model: 'gpt-4',
    defaultParams: [
        'temperature' => 0.7,
        'max_tokens' => 2000,
    ]
);
```

#### Anthropic Claude

```php
use CleverBot\Models\AnthropicModel;

$model = new AnthropicModel(
    apiKey: 'sk-ant-...',
    model: 'claude-3-sonnet-20240229',
    defaultParams: [
        'temperature' => 0.7,
        'max_tokens' => 2000,
    ]
);
```

### Managing Conversation History

```php
// Limit by number of messages
$messageManager = new MessageManager(maxMessages: 10);

// Access message history
$messages = $messageManager->getMessages();

// Add messages manually
$messageManager->addSystemMessage("You are a helpful assistant.");
$messageManager->addUserMessage("Hello!");
$messageManager->addAssistantMessage("Hi there!");
```

### Configuration Options

```php
$config = new AgentConfig(
    maxIterations: 10,      // Maximum recursive tool call iterations
    verbose: true,          // Output execution details
    metadata: [             // Custom metadata
        'user_id' => 123,
        'session_id' => 'abc',
    ]
);
```

## Examples

Full working examples are available in the `examples/` directory:

- `examples/basic-usage.php` - Simple conversation without tools
- `examples/tool-usage.php` - Weather tool integration and multi-turn conversations

Run examples:

```bash
php examples/basic-usage.php
php examples/tool-usage.php
```

## Component Details

### Agent

The `Agent` class is the main orchestrator that:
- Manages the conversation flow
- Coordinates between model and tools
- Handles recursive tool calling
- Enforces iteration limits to prevent infinite loops

### Models

Models provide a unified interface to different LLM providers:

- **ModelInterface**: Common interface for all models
- **ModelResponse**: Unified response format
- **ToolCall**: Representation of a tool call request
- **OpenAIModel**: OpenAI GPT implementation
- **AnthropicModel**: Anthropic Claude implementation

### Tools

Tools extend agent capabilities:

- **ToolInterface**: Common interface for all tools
- **Tool**: Abstract base class with helper methods
- **ToolRegistry**: Central registry for tool management
- **ToolResult**: Standardized result format
- **GetWeatherTool**: Example implementation

### Messages

Message management system:

- **MessageManager**: Manages conversation history with optional limits
- **Message**: Immutable value object for messages
- Supports roles: user, assistant, system, tool

## Current Limitations

This is a foundational implementation focused on core architecture. The following are **not** currently implemented:

- ❌ Real HTTP clients (mock responses are used)
- ❌ Streaming responses
- ❌ Database persistence
- ❌ Multi-agent systems
- ❌ Token counting for message trimming
- ❌ Authentication/authorization layers
- ❌ Rate limiting

These features can be added in future versions based on requirements.

## Development

### Code Standards

- PHP 8.1+ features (readonly properties, property promotion)
- Strict typing (`declare(strict_types=1)`)
- PSR-4 autoloading
- Comprehensive docblocks
- Value objects where appropriate

### Project Structure

```
clever-bot/
├── src/
│   ├── Agent/           # Agent orchestration
│   ├── Messages/        # Message management
│   ├── Models/          # LLM abstractions
│   └── Tools/           # Tool system
│       ├── Examples/    # Example tools
│       └── Exceptions/  # Tool-specific exceptions
├── examples/            # Usage examples
├── composer.json        # Package definition
└── README.md           # This file
```

## Contributing

Contributions are welcome! Please ensure:

1. Code follows existing patterns and standards
2. Strict typing is maintained
3. Docblocks are comprehensive
4. Examples are updated if public APIs change

## License

This package is open-sourced software licensed under the MIT license.

## Credits

Developed by Jonston

## Testing

### Setup

1. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` and add your API keys:
   ```env
   OPENAI_API_KEY=your-openai-api-key
   ANTHROPIC_API_KEY=your-anthropic-api-key
   GEMINI_API_KEY=your-gemini-api-key
   TEST_MODE=false  # Set to true for mock responses
   ```

### Running Tests

Run individual model tests:
```bash
# Test OpenAI integration
php tests/test-runner.php openai

# Test Anthropic integration
php tests/test-runner.php anthropic

# Test Gemini integration
php tests/test-runner.php gemini

# Test agent integration
php tests/test-runner.php agent

# Run all tests
php tests/test-runner.php all
```

### Test Files

- `tests/test-openai.php` - OpenAI model functionality
- `tests/test-anthropic.php` - Anthropic model functionality
- `tests/test-gemini.php` - Gemini model functionality
- `tests/test-agent.php` - Full agent integration testing

---

**Note**: When `TEST_MODE=true` in `.env`, all models will use mock responses for testing. Set to `false` to use real API calls.