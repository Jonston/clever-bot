<?php

declare(strict_types=1);

namespace CleverBot\Models;

/**
 * OpenAI model implementation
 */
class OpenAIModel implements ModelInterface
{
    /**
     * @param string $apiKey OpenAI API key
     * @param string $model Model name (e.g., 'gpt-4', 'gpt-3.5-turbo')
     * @param array<string, mixed> $defaultParams Default parameters for API calls
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4',
        private readonly array $defaultParams = []
    ) {
    }

    /**
     * Generate a response from OpenAI
     *
     * @param array<array<string, mixed>> $messages
     * @param array<array<string, mixed>> $toolDefinitions
     */
    public function generate(array $messages, array $toolDefinitions = []): ModelResponse
    {
        // Prepare the request payload
        $payload = array_merge($this->defaultParams, [
            'model' => $this->model,
            'messages' => $messages,
        ]);

        // Add tools if provided
        if (!empty($toolDefinitions)) {
            $payload['tools'] = $toolDefinitions;
            $payload['tool_choice'] = 'auto';
        }

        // Make HTTP request to OpenAI API (or use mock for testing)
        if ($this->isTestKey()) {
            $mockResponse = $this->createMockResponse($messages, $toolDefinitions);
            return ModelResponse::fromOpenAI($mockResponse);
        }

        $responseData = $this->makeApiRequest('https://api.openai.com/v1/chat/completions', $payload);

        return ModelResponse::fromOpenAI($responseData);
    }

    /**
     * Make HTTP request to OpenAI API
     *
     * @param string $url
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     * @throws \RuntimeException
     */
    private function makeApiRequest(string $url, array $payload): array
    {
        $client = new \GuzzleHttp\Client([
            'timeout' => 60,
        ]);

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode OpenAI API response: ' . json_last_error_msg());
            }

            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $message = $e->getMessage();
            if ($e->hasResponse()) {
                $message .= ': ' . $e->getResponse()->getBody()->getContents();
            }
            throw new \RuntimeException('OpenAI API request failed: ' . $message);
        }
    }

    /**
     * Check if the API key is a test key that should use mock responses
     *
     * @return bool
     */
    private function isTestKey(): bool
    {
        return getenv('TEST_MODE') === 'true' || str_starts_with($this->apiKey, 'test-');
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
                'id' => 'chatcmpl-' . uniqid(),
                'object' => 'chat.completion',
                'created' => time(),
                'model' => $this->model,
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => null,
                            'tool_calls' => [
                                [
                                    'id' => 'call_' . uniqid(),
                                    'type' => 'function',
                                    'function' => [
                                        'name' => 'get_weather',
                                        'arguments' => json_encode([
                                            'location' => 'San Francisco, CA',
                                            'unit' => 'celsius',
                                        ]),
                                    ],
                                ],
                            ],
                        ],
                        'finish_reason' => 'tool_calls',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 50,
                    'completion_tokens' => 20,
                    'total_tokens' => 70,
                ],
            ];
        }

        // Regular text response
        return [
            'id' => 'chatcmpl-' . uniqid(),
            'object' => 'chat.completion',
            'created' => time(),
            'model' => $this->model,
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'This is a mock response from OpenAI. In production, this would be the actual API response.',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 30,
                'completion_tokens' => 15,
                'total_tokens' => 45,
            ],
        ];
    }
}
