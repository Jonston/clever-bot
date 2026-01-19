<?php

declare(strict_types=1);

namespace CleverBot\Exceptions;

/**
 * Exception thrown when configuration is invalid
 */
class ConfigurationException extends CleverBotException
{
    /**
     * Create a missing API key exception
     */
    public static function missingApiKey(string $provider): self
    {
        return new self("Missing API key for provider: {$provider}. Please set the API key in config/clever-bot.php or your .env file.");
    }

    /**
     * Create an unknown provider exception
     */
    public static function unknownProvider(string $provider): self
    {
        return new self("Unknown provider: {$provider}. Supported providers are: openai, anthropic, gemini.");
    }
}
