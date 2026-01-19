<?php

declare(strict_types=1);

namespace CleverBot\Exceptions;

/**
 * Exception thrown when model operations fail
 */
class ModelException extends CleverBotException
{
    /**
     * Create an API error exception
     */
    public static function apiError(string $provider, string $message): self
    {
        return new self("API Error ({$provider}): {$message}");
    }

    /**
     * Create an invalid response exception
     */
    public static function invalidResponse(string $provider): self
    {
        return new self("Invalid response from {$provider}");
    }
}
