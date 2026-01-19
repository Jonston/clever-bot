<?php

declare(strict_types=1);

namespace CleverBot\Tests\Feature;

use CleverBot\Agent\Agent;
use CleverBot\Agent\AgentConfig;
use CleverBot\Messages\MessageManager;
use CleverBot\Models\GeminiModel;
use CleverBot\Models\Product;
use CleverBot\Tests\TestCase;
use CleverBot\Tools\ListProductsTool;
use CleverBot\Tools\ToolRegistry;
use CleverBot\Tools\UpdateProductTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

/**
 * Feature test for Gemini Agent with real database integration
 */
class GeminiAgentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function test_gemini_agent_applies_discount_to_smartphones_over_1000(): void
    {
        // Arrange: Create 5 products dynamically
        // 2 smartphones over 1000 (should get discount)
        $iphone = Product::create([
            'name' => 'iPhone 15 Pro',
            'category' => 'smartphones',
            'price' => 1200.00,
            'discount' => 0,
        ]);

        $samsung = Product::create([
            'name' => 'Samsung Galaxy S24 Ultra',
            'category' => 'smartphones',
            'price' => 1300.00,
            'discount' => 0,
        ]);

        // 1 smartphone under 1000 (should NOT get discount)
        Product::create([
            'name' => 'Google Pixel 8',
            'category' => 'smartphones',
            'price' => 699.00,
            'discount' => 0,
        ]);

        // 2 other category products
        Product::create([
            'name' => 'MacBook Pro',
            'category' => 'laptops',
            'price' => 2500.00,
            'discount' => 0,
        ]);

        Product::create([
            'name' => 'Sony WH-1000XM5',
            'category' => 'headphones',
            'price' => 400.00,
            'discount' => 0,
        ]);

        // Setup tools
        $toolRegistry = new ToolRegistry();
        $toolRegistry->register(new ListProductsTool());
        $toolRegistry->register(new UpdateProductTool());

        // Setup Gemini model with test key (will use mock)
        $apiKey = getenv('GEMINI_API_KEY') ?: 'test-key';
        $geminiModel = new GeminiModel($apiKey, 'gemini-2.5-flash');

        // Setup Agent with verbose output
        $messageManager = new MessageManager();
        $messageManager->addSystemMessage(
            'You are an AI assistant with access to tools. Use the available tools to complete user requests. ' .
            'When you need to list products or update them, call the appropriate tools.'
        );

        $agentConfig = new AgentConfig(verbose: true, maxIterations: 10);
        $agent = new Agent('gemini-test-agent', $geminiModel, $toolRegistry, $messageManager, $agentConfig);

        // Act: Execute agent with task
        $userInput = 'Use the list_products tool to get all products, then apply a 10% discount to smartphones with price over 1000 dollars.';
        
        Event::fake(); // Fake events to avoid side effects
        
        $response = $agent->execute($userInput);

        // Assert: Check response
        $this->assertNotNull($response->content);
        $this->assertGreaterThan(0, $response->metadata['iterations'] ?? 0);
        $this->assertNotEmpty($response->metadata['tool_calls'] ?? []);

        // Verify that tool calls were made
        $toolCalls = $response->metadata['tool_calls'] ?? [];
        $toolNames = array_column($toolCalls, 'tool');
        
        $this->assertContains('list_products', $toolNames, 'list_products should have been called');
        
        // Count update_product calls
        $updateCalls = array_filter($toolNames, fn($name) => $name === 'update_product');
        $this->assertGreaterThanOrEqual(2, count($updateCalls), 'Should have called update_product at least twice');

        // Refresh products from database
        $iphone->refresh();
        $samsung->refresh();

        // Assert: Verify discounts were applied
        $this->assertGreaterThan(0, $iphone->discount, 'iPhone 15 Pro should have discount applied');
        $this->assertGreaterThan(0, $samsung->discount, 'Samsung Galaxy S24 Ultra should have discount applied');

        // Verify discount amounts (should be around 10%)
        $this->assertEquals(120, $iphone->discount, 'iPhone discount should be 120 (10% of 1200)', 1);
        $this->assertEquals(130, $samsung->discount, 'Samsung discount should be 130 (10% of 1300)', 1);

        // Verify cheaper smartphone was NOT updated
        $pixel = Product::where('name', 'Google Pixel 8')->first();
        $this->assertEquals(0, $pixel->discount, 'Google Pixel 8 should not have discount');

        // Verify other categories were NOT updated
        $macbook = Product::where('name', 'MacBook Pro')->first();
        $headphones = Product::where('name', 'Sony WH-1000XM5')->first();
        $this->assertEquals(0, $macbook->discount, 'MacBook should not have discount');
        $this->assertEquals(0, $headphones->discount, 'Headphones should not have discount');
    }

    public function test_gemini_agent_handles_empty_product_list(): void
    {
        // Arrange: No products in database
        $toolRegistry = new ToolRegistry();
        $toolRegistry->register(new ListProductsTool());
        $toolRegistry->register(new UpdateProductTool());

        $geminiModel = new GeminiModel('test-key', 'gemini-2.5-flash');
        $messageManager = new MessageManager();
        $agentConfig = new AgentConfig(verbose: false, maxIterations: 5);
        $agent = new Agent('gemini-test-agent', $geminiModel, $toolRegistry, $messageManager, $agentConfig);

        Event::fake();

        // Act
        $response = $agent->execute('List all products.');

        // Assert
        $this->assertNotNull($response->content);
        
        // Verify list_products was called
        $toolCalls = $response->metadata['tool_calls'] ?? [];
        $toolNames = array_column($toolCalls, 'tool');
        $this->assertContains('list_products', $toolNames);
    }
}
