<?php

declare(strict_types=1);

namespace CleverBot\Models;

/**
 * Unified response from any LLM model
 */
final readonly class ModelResponse
{
    /**
     * @param string|null $content Text content from the model
     * @param ToolCall[] $toolCalls Tool calls requested by the model
     * @param array<string, mixed> $metadata Additional metadata from the model response
     */
    public function __construct(
        public ?string $content = null,
        public array $toolCalls = [],
        public array $metadata = []
    ) {
    }

    /**
     * Check if the response contains tool calls
     */
    public function hasToolCalls(): bool
    {
        return !empty($this->toolCalls);
    }

    /**
     * Get all tool calls
     *
     * @return ToolCall[]
     */
    public function getToolCalls(): array
    {
        return $this->toolCalls;
    }

    /**
     * Get the content from the response
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Create ModelResponse from OpenAI API response format
     *
     * @param array<string, mixed> $response
     */
    public static function fromOpenAI(array $response): self
    {
        $message = $response['choices'][0]['message'] ?? [];
        $content = $message['content'] ?? null;
        $toolCalls = [];

        if (isset($message['tool_calls'])) {
            foreach ($message['tool_calls'] as $toolCall) {
                $arguments = json_decode($toolCall['function']['arguments'], true);
                $toolCalls[] = new ToolCall(
                    id: $toolCall['id'],
                    name: $toolCall['function']['name'],
                    arguments: is_array($arguments) ? $arguments : []
                );
            }
        }

        return new self(
            content: $content,
            toolCalls: $toolCalls,
            metadata: [
                'model' => $response['model'] ?? null,
                'usage' => $response['usage'] ?? null,
                'finish_reason' => $response['choices'][0]['finish_reason'] ?? null,
            ]
        );
    }

    /**
     * Create ModelResponse from Anthropic API response format
     *
     * @param array<string, mixed> $response
     */
    public static function fromAnthropic(array $response): self
    {
        $content = null;
        $toolCalls = [];

        // Anthropic returns content as an array of content blocks
        if (isset($response['content'])) {
            foreach ($response['content'] as $block) {
                if ($block['type'] === 'text') {
                    $content = ($content ?? '') . $block['text'];
                } elseif ($block['type'] === 'tool_use') {
                    $toolCalls[] = new ToolCall(
                        id: $block['id'],
                        name: $block['name'],
                        arguments: $block['input']
                    );
                }
            }
        }

        return new self(
            content: $content,
            toolCalls: $toolCalls,
            metadata: [
                'model' => $response['model'] ?? null,
                'usage' => $response['usage'] ?? null,
                'stop_reason' => $response['stop_reason'] ?? null,
            ]
        );
    }

    /**
     * Create ModelResponse from Gemini API response format
     *
     * @param array<string, mixed> $response
     */
    public static function fromGemini(array $response): self
    {
        $content = null;
        $toolCalls = [];

        $candidate = $response['candidates'][0] ?? [];
        if (isset($candidate['content']['parts'])) {
            foreach ($candidate['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $content = ($content ?? '') . $part['text'];
                } elseif (isset($part['functionCall'])) {
                    $functionCall = $part['functionCall'];
                    $toolCalls[] = new ToolCall(
                        id: $functionCall['name'] . '_' . uniqid(), // Gemini may not have id, generate one
                        name: $functionCall['name'],
                        arguments: $functionCall['args'] ?? []
                    );
                }
            }
        }

        return new self(
            content: $content,
            toolCalls: $toolCalls,
            metadata: [
                'model' => $response['modelVersion'] ?? null,
                'usage' => $response['usageMetadata'] ?? null,
                'finish_reason' => $candidate['finishReason'] ?? null,
            ]
        );
    }
}
