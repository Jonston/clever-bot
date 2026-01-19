<?php

declare(strict_types=1);

namespace CleverBot\Tools\Examples;

use CleverBot\Tools\Tool;
use CleverBot\Tools\ToolResult;

/**
 * Example tool that returns weather information for a given location
 */
class GetWeatherTool extends Tool
{
    public function getName(): string
    {
        return 'get_weather';
    }

    public function getDescription(): string
    {
        return 'Get the current weather for a specific location';
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The city and state, e.g. San Francisco, CA',
                ],
                'unit' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                    'description' => 'The unit of temperature',
                ],
            ],
            'required' => ['location'],
        ];
    }

    /**
     * Execute the weather tool with mock data
     *
     * @param array<string, mixed> $arguments
     */
    public function execute(array $arguments): mixed
    {
        $location = $arguments['location'] ?? 'Unknown';
        $unit = $arguments['unit'] ?? 'celsius';

        // Mock weather data - in real implementation, this would call a weather API
        $mockTemperatures = [
            'celsius' => rand(15, 30),
            'fahrenheit' => rand(59, 86),
        ];

        $conditions = ['sunny', 'cloudy', 'rainy', 'partly cloudy'];
        $condition = $conditions[array_rand($conditions)];

        $weatherData = [
            'location' => $location,
            'temperature' => $mockTemperatures[$unit],
            'unit' => $unit,
            'condition' => $condition,
            'humidity' => rand(30, 80) . '%',
            'wind_speed' => rand(5, 25) . ' km/h',
        ];

        return ToolResult::success($weatherData);
    }
}
