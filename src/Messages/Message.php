<?php

declare(strict_types=1);

namespace CleverBot\Messages;

/**
 * Value object representing a message in the conversation
 */
final readonly class Message
{
    /**
     * @param string $role Message role (user, assistant, system, tool)
     * @param string $content Message content
     * @param array<string, mixed> $metadata Additional metadata (e.g., tool_call_id, name)
     */
    private function __construct(
        public string $role,
        public string $content,
        public array $metadata = []
    ) {
    }

    /**
     * Create a user message
     */
    public static function user(string $content): self
    {
        return new self('user', $content);
    }

    /**
     * Create an assistant message
     */
    public static function assistant(string $content, array $metadata = []): self
    {
        return new self('assistant', $content, $metadata);
    }

    /**
     * Create a system message
     */
    public static function system(string $content): self
    {
        return new self('system', $content);
    }

    /**
     * Create a tool result message
     */
    public static function tool(string $content, string $toolCallId, string $name): self
    {
        return new self('tool', $content, [
            'tool_call_id' => $toolCallId,
            'name' => $name,
        ]);
    }

    /**
     * Convert message to array format for API consumption
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [
            'role' => $this->role,
            'content' => $this->content,
        ];

        if (!empty($this->metadata)) {
            $array = array_merge($array, $this->metadata);
        }

        return $array;
    }
}
