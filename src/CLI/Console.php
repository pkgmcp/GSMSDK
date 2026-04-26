<?php

declare(strict_types=1);

namespace GSMSDK\CLI;

use GSMSDK\Core\Application;
use GSMSDK\Exceptions\GSMSDKException;

/**
 * CLI Console Application
 */
class Console
{
    /** @var array<string, callable> Registered commands */
    private array $commands = [];

    public function __construct(private Application $app)
    {
        $this->registerDefaultCommands();
    }

    /**
     * Register a CLI command
     */
    public function command(string $name, string $description, callable $handler): self
    {
        $this->commands[$name] = [
            'description' => $description,
            'handler' => $handler,
        ];
        return $this;
    }

    /**
     * Run the CLI application
     */
    public function run(array $argv): void
    {
        $command = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        if ($command === 'help' || $command === '--help' || $command === '-h') {
            $this->showHelp();
            return;
        }

        if (!isset($this->commands[$command])) {
            $this->error("Unknown command: {$command}");
            $this->showHelp();
            return;
        }

        try {
            ($this->commands[$command]['handler'])($args);
        } catch (\Throwable $e) {
            $this->error("Command failed: " . $e->getMessage());
            exit(1);
        }
    }

    /**
     * Display help information
     */
    private function showHelp(): void
    {
        echo "GSMSDK CLI Console v" . $this->app->version() . "\n\n";
        echo "Usage:\n";
        echo "  php console.php <command> [arguments]\n\n";
        echo "Available commands:\n";

        foreach ($this->commands as $name => $command) {
            echo sprintf("  %-20s %s\n", $name, $command['description']);
        }

        echo "\n  help                 Show this help message\n";
    }

    /**
     * Display error message
     */
    private function error(string $message): void
    {
        fwrite(STDERR, "Error: {$message}\n");
    }

    /**
     * Register default commands
     */
    private function registerDefaultCommands(): void
    {
        $this->command('version', 'Show application version', function () {
            echo "GSMSDK v" . $this->app->version() . "\n";
            echo "Environment: " . $this->app->environment() . "\n";
        });

        $this->command('status', 'Show application status', function () {
            echo "Application: " . $this->app . "\n";
            echo "Environment: " . $this->app->environment() . "\n";
            echo "Debug Mode: " . ($this->app->config('debug', false) ? 'Enabled' : 'Disabled') . "\n";
        });
    }
}
