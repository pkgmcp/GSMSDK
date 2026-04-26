<?php
/**
 * CLI Console Example
 */

use GSMSDK\CLI\Console;
use GSMSDK\Core\Application;

require __DIR__ . '/../vendor/autoload.php';

// Create application
$app = new Application([
    'debug' => true,
    'environment' => 'development',
]);

// Initialize console
$console = new Console($app);

// Register custom command
$console->command('greet', 'Greet a user by name', function ($args) {
    $name = $args[0] ?? 'World';
    echo "Hello, {$name}!\n";
});

$console->command('db:seed', 'Seed the database with test data', function ($args) {
    echo "Seeding database...\n";
    // Add your seeding logic here
    echo "Database seeded successfully!\n";
});

// Run console
$console->run($argv);
