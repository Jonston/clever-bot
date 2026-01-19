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
];
