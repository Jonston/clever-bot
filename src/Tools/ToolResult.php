<?php

declare(strict_types=1);

namespace CleverBot\Tools;

/**
 * Value object representing the result of a tool execution
 */
final readonly class ToolResult
{
    /**
     * @param mixed $data The result data
     * @param bool $success Whether the execution was successful
     * @param string|null $error Error message if execution failed
     */
    public function __construct(
        public mixed $data,
        public bool $success = true,
        public ?string $error = null
    ) {
    }

    /**
     * Create a successful result
     */
    public static function success(mixed $data): self
    {
        return new self($data, true, null);
    }

    /**
     * Create a failed result
     */
    public static function failure(string $error): self
    {
        return new self(null, false, $error);
    }

    /**
     * Convert result to string format
     */
    public function toString(): string
    {
        if (!$this->success) {
            return "Error: {$this->error}";
        }

        if (is_string($this->data)) {
            return $this->data;
        }

        return json_encode($this->data, JSON_PRETTY_PRINT) ?: 'null';
    }
}
