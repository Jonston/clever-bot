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

### Laravel Integration

If you're using Laravel, the package will automatically register its service provider. To set up the package:

1. **Publish the configuration file:**

```bash
php artisan vendor:publish --tag=clever-bot-config
```

Or use the install command:

```bash
php artisan clever-bot:install
```

2. **Add your API keys to `.env`:**

```env
# Choose your default provider
CLEVER_BOT_PROVIDER=openai

# Add your API keys
OPENAI_API_KEY=your-openai-key-here
ANTHROPIC_API_KEY=your-anthropic-key-here
GEMINI_API_KEY=your-gemini-key-here

# Optional: Customize models
OPENAI_MODEL=gpt-4
ANTHROPIC_MODEL=claude-3-opus-20240229
GEMINI_MODEL=gemini-2.5-flash

# Optional: Configure logging
CLEVER_BOT_LOGGING_ENABLED=true
CLEVER_BOT_LOG_CHANNEL=stack

# Optional: Configure caching
CLEVER_BOT_CACHE_ENABLED=true
CLEVER_BOT_CACHE_DRIVER=redis
```

3. **Test your connection:**

```bash
php artisan clever-bot:test
# Or test a specific provider
php artisan clever-bot:test openai
```

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x (optional, uses illuminate/support)

## Laravel Usage

### Using the Facade

The easiest way to use Clever Bot in Laravel is through the facade:

```php
use CleverBot\Facades\CleverBot;

// Simple question
$response = CleverBot::ask("What is the capital of France?");
echo $response->getContent();

// Using specific tools
use CleverBot\Tools\Examples\GetWeatherTool;

$agent = CleverBot::withTools([
    new GetWeatherTool(),
]);

$response = $agent->execute("What's the weather in London?");
echo $response->getContent();

// Using a different model
$agent = CleverBot::withModel('anthropic', 'claude-3-sonnet-20240229');
$response = $agent->execute("Explain quantum computing");
echo $response->getContent();
```

### Dependency Injection

You can also inject the dependencies directly:

```php
use CleverBot\Agent\Agent;
use CleverBot\AgentFactory;

class ChatController extends Controller
{
    public function __construct(
        private AgentFactory $agentFactory
    ) {}
    
    public function ask(Request $request)
    {
        $response = $this->agentFactory->ask($request->input('question'));
        
        return response()->json([
            'answer' => $response->getContent(),
            'metadata' => $response->metadata,
        ]);
    }
}
```

### Listening to Events

Clever Bot dispatches events during execution that you can listen to:

```php
// In your EventServiceProvider.php
use CleverBot\Events\{
    AgentStarted,
    AgentThinking,
    ToolExecuting,
    ToolExecuted,
    AgentResponding,
    AgentCompleted,
    AgentFailed
};

protected $listen = [
    AgentStarted::class => [
        LogAgentExecution::class,
    ],
    AgentFailed::class => [
        NotifyAdminOfFailure::class,
    ],
    ToolExecuted::class => [
        RecordToolUsage::class,
    ],
];
```

Example listener:

```php
namespace App\Listeners;

use CleverBot\Events\AgentCompleted;
use Illuminate\Support\Facades\Log;

class LogAgentExecution
{
    public function handle(AgentCompleted $event): void
    {
        Log::info('Agent execution completed', [
            'agent' => $event->agentName,
            'duration' => $event->totalTime,
            'tools_used' => $event->toolsExecuted,
        ]);
    }
}
```

### Configuration

After publishing, you can customize the package behavior in `config/clever-bot.php`:

```php
return [
    // Default AI provider
    'default_provider' => env('CLEVER_BOT_PROVIDER', 'openai'),
    
    // Provider configurations
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ],
        // ... other providers
    ],
    
    // Limits
    'limits' => [
        'max_messages' => 50,
        'max_tokens' => 4000,
    ],
    
    // Cache settings
    'cache' => [
        'enabled' => true,
        'driver' => env('CLEVER_BOT_CACHE_DRIVER', 'redis'),
        'ttl' => 3600,
        'prefix' => 'clever_bot',
    ],
    
    // Logging
    'logging' => [
        'enabled' => true,
        'channel' => env('CLEVER_BOT_LOG_CHANNEL', 'stack'),
    ],
];
```

## Tools Configuration

### Automatic Tool Registration

Register tools in `config/clever-bot.php` to make them available to all agents automatically:

```php
'tools' => [
    \App\CleverBot\Tools\GetWeatherTool::class,
    \App\CleverBot\Tools\SearchTool::class,
    
    // With constructor parameters:
    \App\CleverBot\Tools\DatabaseQueryTool::class => [
        'connection' => 'mysql',
        'max_results' => 100,
    ],
],
```

### Tool Presets

Create named tool sets for different use cases:

```php
'tool_presets' => [
    'support' => [
        \App\CleverBot\Tools\GetOrderStatusTool::class,
        \App\CleverBot\Tools\CreateTicketTool::class,
    ],
],
```

Usage:

```php
// Default agent with all tools from config
public function __construct(private Agent $agent) {}

// Agent with specific preset
public function __construct(ToolRegistryBuilder $builder, ModelInterface $model)
{
    $toolRegistry = $builder->buildFromConfig('support');
    
    $this->agent = new Agent(
        name: 'support-bot',
        model: $model,
        toolRegistry: $toolRegistry,
        messageManager: new MessageManager(50, 4000),
        config: new AgentConfig()
    );
}
```

### Creating Custom Tools

See existing examples in `src/Tools/Examples/` directory.

## Standalone PHP Usage

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

Tools are the way to extend your agent's capabilities. Here are examples of different types of tools:

#### Simple Calculation Tool

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

#### Database Integration Tool (Laravel)

For Laravel applications, you can create tools that interact with your database:

```php
use CleverBot\Tools\Tool;
use CleverBot\Tools\ToolResult;
use App\Models\Product;

class ListProductsTool extends Tool
{
    public function getName(): string
    {
        return 'list_products';
    }

    public function getDescription(): string
    {
        return 'Get a list of all available products from the database';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[], // No parameters required
        ];
    }

    public function execute(array $arguments): ToolResult
    {
        $products = Product::all()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
            ];
        })->toArray();

        return new ToolResult($products);
    }
}
```

```php
class UpdateProductTool extends Tool
{
    public function getName(): string
    {
        return 'update_product';
    }

    public function getDescription(): string
    {
        return 'Update a product in the database';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'The product ID to update',
                ],
                'updates' => [
                    'type' => 'object',
                    'description' => 'Fields to update',
                    'properties' => [
                        'price' => ['type' => 'number'],
                        'discount' => ['type' => 'number'],
                    ],
                ],
            ],
            'required' => ['id', 'updates'],
        ];
    }

    public function execute(array $arguments): ToolResult
    {
        $product = Product::findOrFail($arguments['id']);
        $product->update($arguments['updates']);

        return new ToolResult([
            'id' => $product->id,
            'updated' => true,
            'product' => $product->toArray(),
        ]);
    }
}
```

**Usage Example**:
```php
use CleverBot\Facades\CleverBot;

$agent = CleverBot::withTools([
    new ListProductsTool(),
    new UpdateProductTool(),
]);

$response = $agent->execute(
    'Apply a 10% discount to all smartphones priced over $1000'
);

// The agent will:
// 1. Call list_products to fetch all products
// 2. Analyze which products match the criteria
// 3. Call update_product for each matching product
// 4. Return a natural language response
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

### Events (Laravel)

When using Laravel, Clever Bot dispatches events during agent execution:

- **AgentStarted**: Dispatched when an agent begins execution
- **AgentThinking**: Dispatched before the model generates a response
- **ToolExecuting**: Dispatched before a tool is executed
- **ToolExecuted**: Dispatched after a tool completes execution
- **AgentResponding**: Dispatched before the agent returns a response
- **AgentCompleted**: Dispatched when execution completes successfully
- **AgentFailed**: Dispatched when an error occurs during execution

All events are located in the `CleverBot\Events` namespace and can be listened to using Laravel's event system.

### Exceptions

The package provides a hierarchy of exceptions for better error handling:

- **CleverBotException**: Base exception for all package exceptions
- **ModelException**: Thrown when model operations fail (API errors, invalid responses)
- **ToolExecutionException**: Thrown when tool execution fails
- **ConfigurationException**: Thrown when configuration is invalid or incomplete

## Current Limitations

This is a foundational implementation focused on core architecture. The following features are **not** currently implemented but can be added in future versions:

- ❌ Streaming responses
- ❌ Database persistence for conversation history
- ❌ Multi-agent systems
- ❌ Token counting for intelligent message trimming
- ❌ Rate limiting
- ❌ Built-in authentication/authorization layers

**Note**: Real HTTP clients with Guzzle are now implemented for all supported models (OpenAI, Anthropic, Gemini).

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

## Artisan Commands

Clever Bot provides several artisan commands to help you manage the package:

### Install Command

Publish the configuration file and display setup instructions:

```bash
php artisan clever-bot:install
```

This command will:
- Publish the `config/clever-bot.php` configuration file
- Display next steps for configuration

### Test Connection Command

Test your API connection to verify your configuration:

```bash
# Test the default provider
php artisan clever-bot:test

# Test a specific provider
php artisan clever-bot:test openai
php artisan clever-bot:test anthropic
php artisan clever-bot:test gemini
```

This command will:
- Validate your API key configuration
- Send a test request to the AI provider
- Display the response or error message

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

#### PHPUnit Tests (Laravel Integration)

The package includes a comprehensive PHPUnit test suite for Laravel integration:

```bash
# Run all tests
composer test

# Or use phpunit directly
./vendor/bin/phpunit

# Run with detailed output
./vendor/bin/phpunit --testdox

# Run specific test file
./vendor/bin/phpunit tests/Feature/GeminiAgentTest.php
```

Test coverage includes:
- Service provider registration and bindings
- Configuration loading and merging
- Facade functionality
- Event dispatching
- Command registration
- **Gemini Agent with real database integration** (see GeminiAgentTest)

#### Gemini Agent Database Integration Test

The `GeminiAgentTest` demonstrates a real-world scenario where the Gemini agent:

1. **Creates test data**: Dynamically creates 5 products in SQLite database
   - 2 smartphones over $1000 (iPhone 15 Pro, Samsung Galaxy S24 Ultra)
   - 1 smartphone under $1000 (Google Pixel 8)
   - 2 other category products (MacBook Pro, Sony headphones)

2. **Executes tools sequentially**:
   - Calls `list_products` to fetch all products from database
   - Analyzes which products match criteria (smartphones > $1000)
   - Calls `update_product` twice in parallel to apply 10% discount

3. **Verifies results**:
   - Checks that both expensive smartphones received discount
   - Confirms cheaper products were not modified
   - Validates the exact discount amounts in database

This test showcases:
- Real database integration with Eloquent models
- Sequential tool execution (list → analyze → update)
- Parallel tool calls (updating multiple products)
- Database migrations and transactions with RefreshDatabase trait

**Files involved**:
- `tests/Feature/GeminiAgentTest.php` - Main test file
- `database/migrations/2026_01_19_000001_create_products_table.php` - Products table migration
- `src/Models/Product.php` - Eloquent Product model
- `src/Tools/ListProductsTool.php` - Tool for listing products from DB
- `src/Tools/UpdateProductTool.php` - Tool for updating products in DB

---

**Note**: Tests use SQLite in-memory database by default for speed. The test data is created dynamically in each test method using Laravel factories and Eloquent models.