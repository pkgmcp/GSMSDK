<?php
/**
 * Example: Using GSM make:view command
 * 
 * This script demonstrates how to use the GSM CLI tool
 * to create new view templates.
 * 
 * Usage:
 *   php gsm make:view <name> [options]
 *   
 * Examples:
 *   php gsm make:view admin.dashboard
 *   php gsm make:view components.alert --component
 *   php gsm make:view layouts.master --layout
 *   php gsm make:view pages.home --section
 */

// Example 1: Create a dashboard view
echo "Example 1: Creating admin dashboard view\n";
echo shell_exec('php gsm make:view admin.dashboard 2>&1');

// Example 2: Create a component
echo "\nExample 2: Creating alert component\n";
echo shell_exec('php gsm make:view components.alert --component 2>&1');

// Example 3: Create a layout
echo "\nExample 3: Creating admin layout\n";
echo shell_exec('php gsm make:view layouts.admin --layout 2>&1');

// Example 4: Create a section view
echo "\nExample 4: Creating home page with section\n";
echo shell_exec('php gsm make:view pages.home --section 2>&1');

echo "\nAll examples completed!\n";
echo "Check resources/views/ for created files.\n";
