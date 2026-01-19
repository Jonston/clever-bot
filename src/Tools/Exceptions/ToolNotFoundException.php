<?php

declare(strict_types=1);

namespace CleverBot\Tools\Exceptions;

use Exception;

/**
 * Exception thrown when a requested tool is not found in the registry
 */
class ToolNotFoundException extends Exception
{
    public function __construct(string $toolName)
    {
        parent::__construct("Tool not found: {$toolName}");
    }
}
