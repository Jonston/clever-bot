<?php

declare(strict_types=1);

namespace CleverBot\Console\Commands;

use Illuminate\Console\Command;

/**
 * Command to install Clever Bot package
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clever-bot:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Clever Bot package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Installing Clever Bot...');
        $this->newLine();

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'clever-bot-config',
            '--force' => false,
        ]);

        $this->newLine();
        $this->info('✓ Config published');
        $this->info('✓ Clever Bot installed successfully!');
        $this->newLine();
        
        $this->info('Next steps:');
        $this->line('1. Add your API keys to .env file:');
        $this->line('   - OPENAI_API_KEY=your-key');
        $this->line('   - ANTHROPIC_API_KEY=your-key');
        $this->line('   - GEMINI_API_KEY=your-key');
        $this->line('2. Configure config/clever-bot.php if needed');
        $this->line('3. Test your connection: php artisan clever-bot:test');
        $this->newLine();

        return self::SUCCESS;
    }
}
