<?php

declare(strict_types=1);

namespace CleverBot\Tools;

use CleverBot\Models\Product;

/**
 * Tool for listing products from database
 */
class ListProductsTool extends Tool
{
    public function getName(): string
    {
        return 'list_products';
    }

    public function getDescription(): string
    {
        return 'Get a list of all available products from the database';
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
        ];
    }

    /**
     * Execute the list products tool with real database data
     *
     * @param array<string, mixed> $arguments
     */
    public function execute(array $arguments): ToolResult
    {
        $products = Product::all()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
                'discount' => $product->discount,
            ];
        })->toArray();

        return new ToolResult($products);
    }
}
