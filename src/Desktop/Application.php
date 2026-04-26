<?php

declare(strict_types=1);

namespace GSMSDK\Desktop;

use GSMSDK\Core\Application as CoreApplication;

/**
 * Desktop Application Entry Point
 */
class Application extends CoreApplication
{
    private ?Window $window = null;

    /**
     * Create desktop window
     */
    public function createWindow(array $config = []): Window
    {
        $this->window = new Window($config);
        return $this->window;
    }

    /**
     * Get desktop window
     */
    public function getWindow(): ?Window
    {
        return $this->window;
    }

    /**
     * Run desktop application
     */
    public function run(): void
    {
        if ($this->window === null) {
            $this->createWindow();
        }

        // Emit event for desktop runtime (Electron, Tauri, etc.)
        $this->emit('ready');
    }

    /**
     * Emit event to desktop runtime
     */
    private function emit(string $event, mixed $data = null): void
    {
        // Can be overridden by desktop runtime integration
        error_log(sprintf('Event: %s, Data: %s', $event, json_encode($data)));
    }
}
