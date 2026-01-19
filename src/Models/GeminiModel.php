<?php

declare(strict_types=1);

namespace CleverBot\Models;

/**
 * Google Gemini model implementation
 */
class GeminiModel implements ModelInterface
{
    /**
     * @param string $apiKey Gemini API key
     * @param string $model Model name (e.g., 'gemini-2.5-flash', 'gemini-2.5-pro')
     * @param array<string, mixed> $defaultParams Default parameters for API calls
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gemini-2.5-flash',
        private readonly array $defaultParams = []
    ) {
    }

    /**
     * Generate a response from Gemini
     *
     * @param array<array<string, mixed>> $messages
     * @param array<array<string, mixed>> $toolDefinitions
     */
    public function generate(array $messages, array $toolDefinitions = []): ModelResponse
    {
        // Transform messages to Gemini format
        $geminiMessages = $this->transformMessages($messages);

        // Prepare the request payload
        $payload = array_merge($this->defaultParams, [
            'contents' => $geminiMessages,
        ]);

        // Transform and add tools if provided
        if (!empty($toolDefinitions)) {
            $payload['tools'] = [
                'functionDeclarations' => $this->transformToolDefinitions($toolDefinitions)
            ];
        }

        // Make HTTP request to Gemini API (or use mock for testing)
        if ($this->isTestKey()) {
            $mockResponse = $this->createMockResponse($geminiMessages, $toolDefinitions);
            return ModelResponse::fromGemini($mockResponse);
        }

        $responseData = $this->makeApiRequest("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", $payload);

        return ModelResponse::fromGemini($responseData);
    }

    /**
     * Make HTTP request to Gemini API
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
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode Gemini API response: ' . json_last_error_msg());
            }

            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $message = $e->getMessage();
            if ($e->hasResponse()) {
                $message .= ': ' . $e->getResponse()->getBody()->getContents();
            }
            throw new \RuntimeException('Gemini API request failed: ' . $message);
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
     * Transform messages from OpenAI format to Gemini format
     *
     * @param array<array<string, mixed>> $messages
     * @return array<array<string, mixed>>
     */
    private function transformMessages(array $messages): array
    {
        $geminiMessages = [];

        foreach ($messages as $message) {
            $role = $message['role'];
            $content = $message['content'] ?? '';

            // Gemini uses 'model' instead of 'assistant'
            if ($role === 'assistant') {
                $role = 'model';
            }

            // Skip system messages for now (Gemini handles system differently)
            if ($role === 'system') {
                continue;
            }

            if ($role === 'tool') {
                // Tool results are added to the previous message's parts
                // For simplicity, add as a new message with functionResponse part
                $geminiMessages[] = [
                    'role' => 'user', // Tool results come from user/system
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $message['name'] ?? '',
                                'response' => ['result' => json_decode($content, true) ?? $content]
                            ]
                        ]
                    ]
                ];
            } else {
                $geminiMessages[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => $content]
                    ]
                ];
            }
        }

        return $geminiMessages;
    }

    /**
     * Transform tool definitions from OpenAI format to Gemini format
     *
     * @param array<array<string, mixed>> $toolDefinitions
     * @return array<array<string, mixed>>
     */
    private function transformToolDefinitions(array $toolDefinitions): array
    {
        $geminiTools = [];

        foreach ($toolDefinitions as $tool) {
            if (isset($tool['function'])) {
                $function = $tool['function'];
                $geminiTools[] = [
                    'name' => $function['name'],
                    'description' => $function['description'] ?? '',
                    'parameters' => $function['parameters'] ?? []
                ];
            }
        }

        return $geminiTools;
    }

    /**
     * Create a mock response for demonstration purposes
     *
     * @param array<array<string, mixed>> $geminiMessages
     * @param array<array<string, mixed>> $toolDefinitions
     * @return array<string, mixed>
     */
    private function createMockResponse(array $geminiMessages, array $toolDefinitions): array
    {
        $lastMessage = end($geminiMessages);

        // If tools are available and user asks about weather, trigger tool call
        if (!empty($toolDefinitions) &&
            isset($lastMessage['parts'][0]['text']) &&
            stripos($lastMessage['parts'][0]['text'], 'weather') !== false) {
            return [
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'functionCall' => [
                                        'name' => 'get_weather',
                                        'args' => [
                                            'location' => 'San Francisco, CA',
                                            'unit' => 'celsius',
                                        ]
                                    ]
                                ]
                            ],
                            'role' => 'model'
                        ],
                        'finishReason' => 'STOP'
                    ]
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 50,
                    'candidatesTokenCount' => 20,
                    'totalTokenCount' => 70,
                ],
                'modelVersion' => $this->model
            ];
        }

        // Regular text response
        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => 'This is a mock response from Gemini. In production, this would be the actual API response.'
                            ]
                        ],
                        'role' => 'model'
                    ],
                    'finishReason' => 'STOP'
                ]
            ],
            'usageMetadata' => [
                'promptTokenCount' => 30,
                'candidatesTokenCount' => 15,
                'totalTokenCount' => 45,
            ],
            'modelVersion' => $this->model
        ];
    }
}