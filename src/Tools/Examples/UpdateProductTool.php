<?php

declare(strict_types=1);

namespace CleverBot\Tools\Examples;

use CleverBot\Tools\Tool;
use CleverBot\Tools\ToolResult;

/**
 * Example tool that updates a product
 */
class UpdateProductTool extends Tool
{
    public function getName(): string
    {
        return 'update_product';
    }

    public function getDescription(): string
    {
        return 'Update a product with new data';
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'The product ID to update',
                ],
                'updates' => [
                    'type' => 'object',
                    'description' => 'The fields to update',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'category' => ['type' => 'string'],
                        'price' => ['type' => 'number'],
                        'discount' => ['type' => 'number'],
                    ],
                ],
            ],
            'required' => ['id', 'updates'],
        ];
    }

    /**
     * Execute the update product tool with mock data
     *
     * @param array<string, mixed> $arguments
     */
    public function execute(array $arguments): ToolResult
    {
        $id = $arguments['id'];
        $updates = $arguments['updates'];

        // Mock updated product
        $updatedProduct = [
            'id' => $id,
            'updated' => true,
            'changes' => $updates,
        ];

        return new ToolResult($updatedProduct);
    }
}