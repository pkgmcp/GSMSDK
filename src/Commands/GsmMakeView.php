<?php

declare(strict_types=1);

namespace GSMSDK\Commands;

use GSMSDK\Core\Command;
use GSMSDK\Core\Engine\GSM;

/**
 * GSM Make View Command
 * 
 * Creates a new GSM template file with scaffolding
 * 
 * Usage:
 *   php gsm make:view admin.dashboard
 *   php gsm make:view components.alert --section
 *   php gsm make:view layouts.master --layout
 */
class GsmMakeView extends Command
{
    /**
     * Command signature
     */
    protected static string $signature = 'gsm:make:view {name} {--layout} {--component} {--section}';
    
    /**
     * Command description
     */
    protected static string $description = 'Create a new GSM template file';
    
    /**
     * Template stubs path
     */
    private string $stubsPath;
    
    /**
     * Views base path
     */
    private string $viewsPath;
    
    /**
     * Handle the command
     */
    public function handle(): int
    {
        $this->stubsPath = dirname(__DIR__, 2) . '/stubs';
        $this->viewsPath = base_path('resources/views');
        
        $name = $this->argument('name');
        $isLayout = $this->option('layout');
        $isComponent = $this->option('component');
        $isSection = $this->option('section');
        
        // Validate name
        if (empty($name)) {
            $this->error('View name is required');
            return self::FAILURE;
        }
        
        // Convert dot notation to path
        $path = str_replace('.', '/', $name) . '.gsm.php';
        $fullPath = $this->viewsPath . '/' . $path;
        
        // Create directory if it doesn't exist
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
            $this->info('Created directory: ' . $directory);
        }
        
        // Check if file already exists
        if (file_exists($fullPath)) {
            $this->error('View already exists: ' . $path);
            return self::FAILURE;
        }
        
        // Generate content based on type
        $content = $this->generateContent($name, $isLayout, $isComponent, $isSection);
        
        // Write file
        file_put_contents($fullPath, $content);
        
        $this->info('Created view: ' . $path);
        
        // Additional messages
        if ($isLayout) {
            $this->comment('\nRemember to include @yield(\'content\') in your layout.');
        }
        
        if ($isComponent) {
            $this->comment('\nComponent created! Use with @component() directive.');
        }
        
        return self::SUCCESS;
    }
    
    /**
     * Generate template content
     */
    private function generateContent(string $name, bool $isLayout, bool $isComponent, bool $isSection): string
    {
        if ($isLayout) {
            return $this->layoutTemplate($name);
        }
        
        if ($isComponent) {
            return $this->componentTemplate($name);
        }
        
        return $this->viewTemplate($name, $isSection);
    }
    
    /**
     * Layout template
     */
    private function layoutTemplate(string $name): string
    {
        return "<?php\n\n/**\n * Layout: {$name}\n * @extends none\n */\n\n?>\n<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>@yield('title') - GSMSDK</title>\n    <link rel=\"stylesheet\" href=\"/css/app.css\">\n</head>\n<body>\n    <div class=\"container\">\n        @include('layouts.partials.header')\n        \n        <main>\n            @yield('content')\n        </main>\n        \n        @include('layouts.partials.footer')\n    </div>\n    \n    <script src=\"/js/app.js\"></script>\n</body>\n</html>\n";
    }
    
    /**
     * Component template
     */
    private function componentTemplate(string $name): string
    {
        $parts = explode('/', $name);
        $componentName = ucfirst(end($parts));
        
        return "<?php\n\n/**\n * Component: {$componentName}\n * Props: \$props\n * Slot: \$slot\n */\n\n?><div class=\"component-{$parts[count(\$parts) - 1]}\">\n    <!-- Component: {$componentName} -->\n    \n    @if(\$slot)\n        <div class=\"slot\">\n            {!! \$slot !!}\n        </div>\n    @endif\n</div>\n";
    }
    
    /**
     * View template
     */
    private function viewTemplate(string $name, bool $isSection): string
    {
        $parts = explode('/', $name);
        $viewName = ucfirst(end($parts));
        
        if ($isSection) {
            return "<?php\n\n/**\n * Section: {$viewName}\n * @extends layouts.master\n */\n\n@extends('layouts.master')\n\n@section('content')\n<div class=\"page-{$parts[count(\$parts) - 1]}\">\n    <h1>{$viewName}</h1>\n    \n    <!-- Content here -->\n    \n</div>\n@endsection\n";
        }
        
        return "<?php\n\n/**\n * View: {$viewName}\n */\n\n?><div class=\"view-{$parts[count(\$parts) - 1]}\">\n    <h1>{$viewName}</h1>\n    \n    <!-- Content here -->\n    \n</div>\n";
    }
    
    /**
     * Command help
     */
    public static function help(): string
    {
        return <<<HELP
\n  GSM Make View Command\n  =====================\n\n  Creates a new GSM template file with proper scaffolding.\n\n  Usage:\n    php gsm make:view <name> [options]\n\n  Arguments:\n    name          View name (dot notation)\n\n  Options:\n    --layout      Create a layout template\n    --component   Create a component template\n    --section     Create a section view\n\n  Examples:\n    php gsm make:view admin.dashboard\n    php gsm make:view admin.users.index\n    php gsm make:view components.alert --component\n    php gsm make:view layouts.master --layout\n    php gsm make:view pages.home --section\n\n  Description:\n    Creates a new .gsm.php template file in resources/views/\n    with the appropriate scaffolding based on the type.\n\n    Use dot notation for nested directories:\n      php gsm make:view admin.users.list\n      → resources/views/admin/users/list.gsm.php\n\nHELP;
    }
}
