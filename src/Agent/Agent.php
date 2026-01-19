<?php

declare(strict_types=1);

namespace CleverBot\Agent;

use CleverBot\Messages\Message;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\ModelInterface;
use CleverBot\Models\ModelResponse;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\ToolResult;

/**
 * Main agent orchestrator that coordinates model, tools, and messages
 */
class Agent
{
    private int $currentIteration = 0;
    
    /** @var array<array<string, mixed>> */
    private array $toolCallHistory = [];

    /**
     * @param string $name Agent name/identifier
     * @param ModelInterface $model LLM model to use
     * @param ToolRegistry $toolRegistry Registry of available tools
     * @param MessageManager $messageManager Message history manager
     * @param AgentConfig $config Agent configuration
     */
    public function __construct(
        private readonly string $name,
        private readonly ModelInterface $model,
        private readonly ToolRegistry $toolRegistry,
        private readonly MessageManager $messageManager,
        private readonly AgentConfig $config = new AgentConfig()
    ) {
    }

    /**
     * Execute the agent with the given input
     *
     * @param string|Message $input User input or message
     * @return AgentResponse Agent's final response
     */
    public function execute(string|Message $input): AgentResponse
    {
        // Reset iteration counter for new execution
        $this->currentIteration = 0;
        $this->toolCallHistory = [];

        // Add user message to conversation
        if ($input instanceof Message) {
            $this->messageManager->addUserMessage($input->content);
        } else {
            $this->messageManager->addUserMessage($input);
        }

        // Start the agent loop
        return $this->executeLoop();
    }

    /**
     * Main execution loop (recursive)
     */
    private function executeLoop(): AgentResponse
    {
        // Check iteration limit
        $this->currentIteration++;
        if ($this->currentIteration > $this->config->maxIterations) {
            return new AgentResponse(
                content: "Maximum iterations reached. The agent stopped to prevent infinite loops.",
                metadata: [
                    'iterations' => $this->currentIteration,
                    'tool_calls' => $this->toolCallHistory,
                    'stopped_reason' => 'max_iterations',
                ]
            );
        }

        // Get tool definitions
        $toolDefinitions = $this->toolRegistry->getDefinitions();

        // Generate model response
        $modelResponse = $this->model->generate(
            $this->messageManager->getMessagesArray(),
            $toolDefinitions
        );

        // If model wants to call tools, handle them and recurse
        if ($modelResponse->hasToolCalls()) {
            return $this->handleToolCalls($modelResponse);
        }

        // No tool calls - return final response
        $content = $modelResponse->getContent() ?? '';
        
        // Add assistant message to history
        $this->messageManager->addAssistantMessage($content);

        return new AgentResponse(
            content: $content,
            metadata: [
                'iterations' => $this->currentIteration,
                'tool_calls' => $this->toolCallHistory,
                'model_metadata' => $modelResponse->metadata,
            ]
        );
    }

    /**
     * Handle tool calls from model response
     */
    private function handleToolCalls(ModelResponse $response): AgentResponse
    {
        $toolResults = [];

        foreach ($response->getToolCalls() as $toolCall) {
            // Record tool call in history
            $this->toolCallHistory[] = [
                'iteration' => $this->currentIteration,
                'tool' => $toolCall->name,
                'arguments' => $toolCall->arguments,
            ];

            if ($this->config->verbose) {
                echo "Calling tool: {$toolCall->name} with arguments: " . json_encode($toolCall->arguments) . "\n";
            }

            try {
                // Execute the tool
                $result = $this->toolRegistry->execute($toolCall->name, $toolCall->arguments);
                
                // Convert result to string
                $resultString = $this->formatToolResult($result);

                $toolResults[] = [
                    'tool_call_id' => $toolCall->id,
                    'name' => $toolCall->name,
                    'content' => $resultString,
                ];

                if ($this->config->verbose) {
                    echo "Tool result: {$resultString}\n";
                }
            } catch (\Exception $e) {
                // Handle tool execution errors
                $errorMessage = "Error executing tool {$toolCall->name}: {$e->getMessage()}";
                
                $toolResults[] = [
                    'tool_call_id' => $toolCall->id,
                    'name' => $toolCall->name,
                    'content' => $errorMessage,
                ];

                if ($this->config->verbose) {
                    echo "Tool error: {$errorMessage}\n";
                }
            }
        }

        // Add assistant message with tool calls (content may be null)
        $this->messageManager->addAssistantMessage(
            $response->getContent() ?? '',
            ['tool_calls' => array_map(fn($tc) => $tc->toArray(), $response->getToolCalls())]
        );

        // Add tool results to message history
        $this->messageManager->addToolResults($toolResults);

        // Recurse to get model's response to tool results
        return $this->executeLoop();
    }

    /**
     * Format tool result for message content
     */
    private function formatToolResult(mixed $result): string
    {
        if ($result instanceof ToolResult) {
            return $result->toString();
        }

        if (is_string($result)) {
            return $result;
        }

        if (is_array($result) || is_object($result)) {
            return json_encode($result, JSON_PRETTY_PRINT);
        }

        return (string) $result;
    }

    /**
     * Get agent name
     */
    public function getName(): string
    {
        return $this->name;
    }
}
