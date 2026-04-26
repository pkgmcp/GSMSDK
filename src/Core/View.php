<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Core\Engine\GSM;
use GSMSDK\Exceptions\\ValidationException;

/**
 * View renderer with GSM engine and TheGridCN theme support
 *
 * Renders GSM templates (similar to Blade) with modern styling
 */
class View
{
    /** @var string Base path to views */
    private string $viewPath;

    /** @var string Base path to layouts */
    private string $layoutPath;

    /** @var array<string, mixed> Shared data */
    private static array $sharedData = [];

    /** @var string Current theme */
    private string $theme = 'thegridcn';

    /** @var GSM Template engine */
    private GSM $engine;

    public function __construct(string $viewPath, string $layoutPath = '')
    {
        $this->viewPath = rtrim($viewPath, '/');
        $this->layoutPath = $layoutPath ? rtrim($layoutPath, '/') : $viewPath . '/layouts';
        $this->engine = new GSM($this->viewPath . '/cache');
    }

    /**
     * Share data with all views
     *
     * @param  array<string, mixed>  $data
     */
    public static function share(array $data): void
    {
        self::$sharedData = array_merge(self::$sharedData, $data);
    }

    /**
     * Set theme
     */
    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Render view
     *
     * @param  array<string, mixed>  $data
     */
    public function render(string $view, array $data = [], ?string $layout = null): string
    {
        $data = array_merge(self::$sharedData, $data);
        
        if ($layout !== null) {
            $data['content'] = $this->engine->render($view, $data);
            return $this->engine->render('layouts/' . $layout, $data);
        }

        return $this->engine->render($view, $data);
    }

    /**
     * Render layout
     *
     * @param  array<string, mixed>  $data
     */
    public function renderLayout(string $layout, array $data = []): string
    {
        $data = array_merge(self::$sharedData, $data);
        return $this->engine->render('layouts/' . $layout, $data);
    }

    /**
     * Render partial/component
     *
     * @param  array<string, mixed>  $data
     */
    public function partial(string $partial, array $data = []): string
    {
        $data = array_merge(self::$sharedData, $data);
        return $this->engine->render('partials/' . $partial, $data);
    }

    /**
     * Generate TheGridCN styled HTML page
     *
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $data
     */
    public function gridPage(string $title, string $content, array $meta = [], array $data = []): string
    {
        $meta = array_merge([
            'description' => 'GSMSDK - Full-Stack PHP Framework',
            'keywords' => 'PHP, framework, fullstack, desktop, mobile',
            'author' => 'GSMSDK Team',
            'theme-color' => '#6366f1',
        ], $meta);

        return $this->engine->render('layouts/thegridcn', array_merge($data, [
            'title' => $title,
            'content' => $content,
            'meta' => $meta,
        ]));
    }

    /**
     * Get asset path
     */
    public function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }

    /**
     * Escape HTML output
     */
    public function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format date
     */
    public function date(string $date, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, strtotime($date));
    }
}
