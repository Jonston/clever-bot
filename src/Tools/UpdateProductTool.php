<?php

declare(strict_types=1);

namespace CleverBot\Tools;

use CleverBot\Models\Product;

/**
 * Tool for updating products in database
 */
class UpdateProductTool extends Tool
{
    public function getName(): string
    {
        return 'update_product';
    }

    public function getDescription(): string
    {
        return 'Update a product in the database with new data';
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
                        'discount_percent' => [
                            'type' => 'number',
                            'description' => 'Percentage discount to apply (will calculate absolute discount from current price)',
                        ],
                    ],
                ],
            ],
            'required' => ['id', 'updates'],
        ];
    }

    /**
     * Execute the update product tool with real database update
     *
     * @param array<string, mixed> $arguments
     */
    public function execute(array $arguments): ToolResult
    {
        $id = $arguments['id'];
        $updates = $arguments['updates'];

        $product = Product::findOrFail($id);

        // Handle discount_percent by calculating absolute discount
        if (isset($updates['discount_percent'])) {
            $updates['discount'] = $product->price * ($updates['discount_percent'] / 100);
            unset($updates['discount_percent']);
        }

        $product->update($updates);

        return new ToolResult([
            'id' => $product->id,
            'updated' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
                'discount' => $product->discount,
            ],
        ]);
    }
}
