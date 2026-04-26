<?php

declare(strict_types=1);

namespace GSMSDK\Desktop;

use GSMSDK\Traits\Configurable;

/**
 * Desktop Window Configuration
 *
 * Handles window properties for desktop applications
 * (electron, PHP-GTK, or web-based desktop wrappers)
 */
class Window
{
    use Configurable;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'title' => 'GSMSDK Application',
            'width' => 1024,
            'height' => 768,
            'resizable' => true,
            'fullscreen' => false,
            'frame' => true,
            'icon' => null,
            'background_color' => '#1a1a1e',
            'web_preferences' => [
                'node_integration' => true,
                'context_isolation' => false,
            ],
        ], $config);
    }

    /**
     * Get window title
     */
    public function getTitle(): string
    {
        return $this->config['title'];
    }

    /**
     * Set window title
     */
    public function setTitle(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * Get window dimensions
     *
     * @return array{width: int, height: int}
     */
    public function getDimensions(): array
    {
        return [
            'width' => $this->config['width'],
            'height' => $this->config['height'],
        ];
    }

    /**
     * Set window dimensions
     */
    public function setDimensions(int $width, int $height): self
    {
        $this->config['width'] = $width;
        $this->config['height'] = $height;
        return $this;
    }

    /**
     * Check if window is resizable
     */
    public function isResizable(): bool
    {
        return $this->config['resizable'];
    }

    /**
     * Set resizable state
     */
    public function setResizable(bool $resizable): self
    {
        $this->config['resizable'] = $resizable;
        return $this;
    }

    /**
     * Check if fullscreen
     */
    public function isFullscreen(): bool
    {
        return $this->config['fullscreen'];
    }

    /**
     * Set fullscreen state
     */
    public function setFullscreen(bool $fullscreen): self
    {
        $this->config['fullscreen'] = $fullscreen;
        return $this;
    }

    /**
     * Toggle fullscreen
     */
    public function toggleFullscreen(): self
    {
        $this->config['fullscreen'] = !$this->config['fullscreen'];
        return $this;
    }

    /**
     * Check if window has frame
     */
    public function hasFrame(): bool
    {
        return $this->config['frame'];
    }

    /**
     * Set frame visibility
     */
    public function setFrame(bool $frame): self
    {
        $this->config['frame'] = $frame;
        return $this;
    }

    /**
     * Get background color
     */
    public function getBackgroundColor(): string
    {
        return $this->config['background_color'];
    }

    /**
     * Set background color
     */
    public function setBackgroundColor(string $color): self
    {
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new \InvalidArgumentException("Invalid color format: {$color}");
        }
        $this->config['background_color'] = $color;
        return $this;
    }

    /**
     * Get web preferences
     *
     * @return array<string, mixed>
     */
    public function getWebPreferences(): array
    {
        return $this->config['web_preferences'];
    }

    /**
     * Set web preference
     */
    public function setWebPreference(string $key, mixed $value): self
    {
        $this->config['web_preferences'][$key] = $value;
        return $this;
    }

    /**
     * Convert to JavaScript/JSON representation
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->config;
    }
}
