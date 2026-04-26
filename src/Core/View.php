<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Exceptions\\ValidationException;

/**
 * View renderer with TheGridCN theme support
 *
 * Renders XHTML views with modern styling
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

    public function __construct(string $viewPath, string $layoutPath = '')
    {
        $this->viewPath = rtrim($viewPath, '/');
        $this->layoutPath = $layoutPath ? rtrim($layoutPath, '/') : $viewPath . '/layouts';
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
        $viewFile = $this->viewPath . '/' . $this->normalizePath($view) . '.xhtml';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        $content = $this->renderFile($viewFile, $data);

        if ($layout !== null) {
            $content = $this->renderLayout($layout, array_merge($data, ['content' => $content]));
        }

        return $content;
    }

    /**
     * Render layout
     *
     * @param  array<string, mixed>  $data
     */
    public function renderLayout(string $layout, array $data = []): string
    {
        $layoutFile = $this->layoutPath . '/' . $this->normalizePath($layout) . '.xhtml';

        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layoutFile}");
        }

        return $this->renderFile($layoutFile, $data);
    }

    /**
     * Render partial/component
     *
     * @param  array<string, mixed>  $data
     */
    public function partial(string $partial, array $data = []): string
    {
        $partialFile = $this->viewPath . '/partials/' . $this->normalizePath($partial) . '.xhtml';

        if (!file_exists($partialFile)) {
            throw new \RuntimeException("Partial not found: {$partialFile}");
        }

        return $this->renderFile($partialFile, $data);
    }

    /**
     * Render file with data extraction
     *
     * @param  array<string, mixed>  $data
     */
    private function renderFile(string $file, array $data): string
    {
        $data = array_merge(self::$sharedData, $data);
        extract($data, EXTR_SKIP);

        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Normalize view path
     */
    private function normalizePath(string $path): string
    {
        return str_replace(['.', '\\'], '/', $path);
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

        ob_start();
        include $this->layoutPath . '/thegridcn.xhtml';
        return ob_get_clean();
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
