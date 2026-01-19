<?php

declare(strict_types=1);

namespace CleverBot\Messages;

/**
 * Manages conversation messages with optional limits
 */
class MessageManager
{
    /** @var Message[] */
    private array $messages = [];

    /**
     * @param int|null $maxMessages Maximum number of messages to keep (null = unlimited)
     * @param int|null $maxTokens Maximum token count (null = unlimited, not implemented)
     */
    public function __construct(
        private readonly ?int $maxMessages = null,
        private readonly ?int $maxTokens = null
    ) {
    }

    /**
     * Add a user message to the conversation
     */
    public function addUserMessage(string $content): self
    {
        $this->messages[] = Message::user($content);
        $this->trimIfNeeded();
        return $this;
    }

    /**
     * Add an assistant message to the conversation
     *
     * @param array<string, mixed> $metadata
     */
    public function addAssistantMessage(string $content, array $metadata = []): self
    {
        $this->messages[] = Message::assistant($content, $metadata);
        $this->trimIfNeeded();
        return $this;
    }

    /**
     * Add a system message to the conversation
     */
    public function addSystemMessage(string $content): self
    {
        $this->messages[] = Message::system($content);
        $this->trimIfNeeded();
        return $this;
    }

    /**
     * Add tool result messages to the conversation
     *
     * @param array<array{tool_call_id: string, name: string, content: string}> $results
     */
    public function addToolResults(array $results): self
    {
        foreach ($results as $result) {
            $this->messages[] = Message::tool(
                $result['content'],
                $result['tool_call_id'],
                $result['name']
            );
        }
        $this->trimIfNeeded();
        return $this;
    }

    /**
     * Get all messages
     *
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Get messages in array format for API consumption
     *
     * @return array<array<string, mixed>>
     */
    public function getMessagesArray(): array
    {
        return array_map(fn(Message $message) => $message->toArray(), $this->messages);
    }

    /**
     * Trim messages if limits are exceeded
     */
    private function trimIfNeeded(): void
    {
        if ($this->maxMessages === null) {
            return;
        }

        if (count($this->messages) > $this->maxMessages) {
            $this->messages = array_slice($this->messages, -$this->maxMessages);
        }

        // Token-based trimming would be implemented here when needed
        // This would require a tokenizer implementation
    }
}
