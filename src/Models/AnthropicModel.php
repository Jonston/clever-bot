<?php

declare(strict_types=1);

namespace CleverBot\Models;

/**
 * Anthropic (Claude) model implementation
 */
class AnthropicModel implements ModelInterface
{
    /**
     * @param string $apiKey Anthropic API key
     * @param string $model Model name (e.g., 'claude-3-opus-20240229', 'claude-3-sonnet-20240229')
     * @param array<string, mixed> $defaultParams Default parameters for API calls
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'claude-3-sonnet-20240229',
        private readonly array $defaultParams = []
    ) {
    }

    /**
     * Generate a response from Anthropic
     *
     * @param array<array<string, mixed>> $messages
     * @param array<array<string, mixed>> $toolDefinitions
     */
    public function generate(array $messages, array $toolDefinitions = []): ModelResponse
    {
        // Transform messages to Anthropic format
        $anthropicMessages = $this->transformMessages($messages);
        
        // Extract system message if present
        $systemMessage = $this->extractSystemMessage($messages);

        // Prepare the request payload
        $payload = array_merge($this->defaultParams, [
            'model' => $this->model,
            'messages' => $anthropicMessages,
            'max_tokens' => $this->defaultParams['max_tokens'] ?? 1024,
        ]);

        if ($systemMessage !== null) {
            $payload['system'] = $systemMessage;
        }

        // Transform and add tools if provided
        if (!empty($toolDefinitions)) {
            $payload['tools'] = $this->transformToolDefinitions($toolDefinitions);
        }

        // HERE: HTTP request to Anthropic API would be made
        // Example:
        // $response = $this->httpClient->post('https://api.anthropic.com/v1/messages', [
        //     'headers' => [
        //         'x-api-key' => $this->apiKey,
        //         'anthropic-version' => '2023-06-01',
        //         'Content-Type' => 'application/json',
        //     ],
        //     'json' => $payload,
        // ]);
        // $responseData = json_decode($response->getBody(), true);

        // Mock response for demonstration
        $mockResponse = $this->createMockResponse($messages, $toolDefinitions);

        return ModelResponse::fromAnthropic($mockResponse);
    }

    /**
     * Transform universal message format to Anthropic format
     *
     * @param array<array<string, mixed>> $messages
     * @return array<array<string, mixed>>
     */
    private function transformMessages(array $messages): array
    {
        $transformed = [];
        
        foreach ($messages as $message) {
            // Skip system messages (handled separately in Anthropic)
            if ($message['role'] === 'system') {
                continue;
            }

            $transformed[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        return $transformed;
    }

    /**
     * Extract system message from messages array
     *
     * @param array<array<string, mixed>> $messages
     */
    private function extractSystemMessage(array $messages): ?string
    {
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                return $message['content'];
            }
        }

        return null;
    }

    /**
     * Transform tool definitions from OpenAI format to Anthropic format
     *
     * @param array<array<string, mixed>> $toolDefinitions
     * @return array<array<string, mixed>>
     */
    private function transformToolDefinitions(array $toolDefinitions): array
    {
        $transformed = [];

        foreach ($toolDefinitions as $tool) {
            $function = $tool['function'] ?? [];
            
            $transformed[] = [
                'name' => $function['name'],
                'description' => $function['description'],
                'input_schema' => $function['parameters'],
            ];
        }

        return $transformed;
    }

    /**
     * Create a mock response for demonstration purposes
     *
     * @param array<array<string, mixed>> $messages
     * @param array<array<string, mixed>> $toolDefinitions
     * @return array<string, mixed>
     */
    private function createMockResponse(array $messages, array $toolDefinitions): array
    {
        $lastMessage = end($messages);

        // If tools are available and user asks about weather, trigger tool call
        if (!empty($toolDefinitions) && 
            isset($lastMessage['content']) && 
            stripos($lastMessage['content'], 'weather') !== false) {
            return [
                'id' => 'msg_' . uniqid(),
                'type' => 'message',
                'role' => 'assistant',
                'model' => $this->model,
                'content' => [
                    [
                        'type' => 'tool_use',
                        'id' => 'toolu_' . uniqid(),
                        'name' => 'get_weather',
                        'input' => [
                            'location' => 'San Francisco, CA',
                            'unit' => 'celsius',
                        ],
                    ],
                ],
                'stop_reason' => 'tool_use',
                'usage' => [
                    'input_tokens' => 50,
                    'output_tokens' => 20,
                ],
            ];
        }

        // Regular text response
        return [
            'id' => 'msg_' . uniqid(),
            'type' => 'message',
            'role' => 'assistant',
            'model' => $this->model,
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'This is a mock response from Anthropic Claude. In production, this would be the actual API response.',
                ],
            ],
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 30,
                'output_tokens' => 15,
            ],
        ];
    }
}
