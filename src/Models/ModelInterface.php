<?php

declare(strict_types=1);

namespace CleverBot\Models;

/**
 * Interface for LLM model implementations
 */
interface ModelInterface
{
    /**
     * Generate a response from the model
     *
     * @param array<array<string, mixed>> $messages Conversation messages
     * @param array<array<string, mixed>> $toolDefinitions Available tool definitions
     * @return ModelResponse Unified model response
     */
    public function generate(array $messages, array $toolDefinitions = []): ModelResponse;
}
