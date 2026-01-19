<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by the
    | Clever Bot package. You may set this to any of the configured providers
    | below: openai, anthropic, or gemini.
    |
    */
    'default_provider' => env('CLEVER_BOT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the AI providers used by your application.
    | Each provider requires an API key and a default model name.
    |
    */
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Message & Token Limits
    |--------------------------------------------------------------------------
    |
    | These limits help prevent excessive API usage and manage conversation
    | history. max_messages controls the conversation history size, while
    | max_tokens sets the maximum response length.
    |
    */
    'limits' => [
        'max_messages' => 50,
        'max_tokens' => 4000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for agent responses. When enabled, identical requests
    | will be served from cache instead of making new API calls.
    |
    */
    'cache' => [
        'enabled' => env('CLEVER_BOT_CACHE_ENABLED', true),
        'driver' => env('CLEVER_BOT_CACHE_DRIVER', 'redis'),
        'ttl' => 3600, // 1 hour in seconds
        'prefix' => 'clever_bot',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Enable logging to track agent executions, errors, and performance.
    | You can specify which Laravel logging channel to use.
    |
    */
    'logging' => [
        'enabled' => env('CLEVER_BOT_LOGGING_ENABLED', true),
        'channel' => env('CLEVER_BOT_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tools Configuration
    |--------------------------------------------------------------------------
    |
    | Register tools that will be automatically available to all agents.
    | You can specify tool classes directly or with constructor parameters.
    |
    | Examples:
    | - Simple: \App\CleverBot\Tools\GetWeatherTool::class
    | - With params: \App\CleverBot\Tools\DatabaseTool::class => ['connection' => 'mysql']
    |
    */
    'tools' => [
        // Example tools (commented out by default)
        // \App\CleverBot\Tools\GetWeatherTool::class,
        // \App\CleverBot\Tools\SearchTool::class,
        // \App\CleverBot\Tools\DatabaseQueryTool::class => [
        //     'connection' => 'mysql',
        //     'max_results' => 100,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tool Presets
    |--------------------------------------------------------------------------
    |
    | Define named sets of tools for different use cases. This allows you
    | to easily create agents with specific capabilities.
    |
    | Usage: $builder->buildFromConfig('support') // loads only support tools
    |
    */
    'tool_presets' => [
        'support' => [
            // Example: customer support tools
            // \App\CleverBot\Tools\GetOrderStatusTool::class,
            // \App\CleverBot\Tools\CreateTicketTool::class,
            // \App\CleverBot\Tools\SearchKnowledgeBaseTool::class,
        ],
        
        'sales' => [
            // Example: sales assistant tools
            // \App\CleverBot\Tools\SearchProductsTool::class,
            // \App\CleverBot\Tools\CheckInventoryTool::class,
            // \App\CleverBot\Tools\CalculateDiscountTool::class,
        ],
        
        'admin' => [
            // Example: admin tools
            // \App\CleverBot\Tools\GenerateReportTool::class,
            // \App\CleverBot\Tools\AnalyzeDataTool::class,
        ],
    ],
];
