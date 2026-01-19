<?php

declare(strict_types=1);

/**
 * CleverBot Test Runner
 * Run individual model tests or all tests
 */

$tests = [
    'openai' => __DIR__ . '/test-openai.php',
    'anthropic' => __DIR__ . '/test-anthropic.php',
    'gemini' => __DIR__ . '/test-gemini.php',
    'agent' => __DIR__ . '/test-agent.php',
];

$runAll = in_array('--all', $argv) || in_array('all', $argv);
$specificTest = null;

foreach ($argv as $arg) {
    if (isset($tests[$arg])) {
        $specificTest = $arg;
        break;
    }
}

echo "=== CleverBot Test Runner ===\n\n";

if ($runAll) {
    echo "Running all tests...\n\n";
    foreach ($tests as $name => $file) {
        runTest($name, $file);
    }
} elseif ($specificTest) {
    runTest($specificTest, $tests[$specificTest]);
} else {
    echo "Usage:\n";
    echo "  php test-runner.php all          # Run all tests\n";
    echo "  php test-runner.php openai       # Run OpenAI test\n";
    echo "  php test-runner.php anthropic    # Run Anthropic test\n";
    echo "  php test-runner.php gemini       # Run Gemini test\n";
    echo "  php test-runner.php agent        # Run Agent integration test\n\n";

    echo "Available tests:\n";
    foreach (array_keys($tests) as $testName) {
        echo "  - $testName\n";
    }
    echo "\n";
}

function runTest(string $name, string $file): void
{
    echo "Running $name test...\n";
    echo str_repeat("-", 50) . "\n";

    if (!file_exists($file)) {
        echo "❌ Test file not found: $file\n\n";
        return;
    }

    $startTime = microtime(true);

    try {
        require $file;
        $executionTime = round(microtime(true) - $startTime, 2);
        echo "\n✅ $name test completed in {$executionTime}s\n\n";
    } catch (Throwable $e) {
        $executionTime = round(microtime(true) - $startTime, 2);
        echo "\n❌ $name test failed in {$executionTime}s: " . $e->getMessage() . "\n\n";
    }
}