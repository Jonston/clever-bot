<?php

declare(strict_types=1);

namespace CleverBot\Tools\Examples;

use CleverBot\Tools\Tool;
use CleverBot\Tools\ToolResult;

/**
 * Example tool that returns a list of products
 */
class ListProductsTool extends Tool
{
    public function getName(): string
    {
        return 'list_products';
    }

    public function getDescription(): string
    {
        return 'Get a list of all available products';
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
     * Execute the list products tool with mock data
     *
     * @param array<string, mixed> $arguments
     */
    public function execute(array $arguments): ToolResult
    {
        // Mock products data
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'category' => 'smartphones', 'price' => 1200],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'category' => 'smartphones', 'price' => 1100],
            ['id' => 3, 'name' => 'MacBook Pro', 'category' => 'laptops', 'price' => 2500],
            ['id' => 4, 'name' => 'Dell XPS 13', 'category' => 'laptops', 'price' => 1500],
            ['id' => 5, 'name' => 'Sony WH-1000XM5', 'category' => 'headphones', 'price' => 400],
            ['id' => 6, 'name' => 'AirPods Pro', 'category' => 'headphones', 'price' => 250],
            ['id' => 7, 'name' => 'iPad Air', 'category' => 'tablets', 'price' => 600],
            ['id' => 8, 'name' => 'Samsung Galaxy Tab S9', 'category' => 'tablets', 'price' => 800],
            ['id' => 9, 'name' => 'Nintendo Switch', 'category' => 'gaming', 'price' => 300],
            ['id' => 10, 'name' => 'PlayStation 5', 'category' => 'gaming', 'price' => 500],
        ];

        return new ToolResult($products);
    }
}