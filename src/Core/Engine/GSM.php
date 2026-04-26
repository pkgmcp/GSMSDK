<?php

declare(strict_types=1);

namespace GSMSDK\Core\Engine;

/**
 * GSM Templating Engine
 *
 * A Blade-inspired templating engine for GSMSDK
 * Compiles GSM templates to native PHP code
 *
 * Syntax:
 *  {{ $variable }}          - Echo escaped
 *  {!! $variable !!}        - Echo raw
 *  @if ($condition)         - If statement
 *  @else                    - Else statement
 *  @endif                   - End if
 *  @foreach ($items as $i)  - Foreach loop
 *  @endforeach              - End foreach
 *  @for ($i = 0; $i < 10; $i++) - For loop
 *  @endfor                  - End for
 *  @while ($condition)      - While loop
 *  @endwhile                - End while
 *  @include('partial')      - Include partial
 *  @extends('layout')       - Extend layout
 *  @section('name')         - Define section
 *  @endsection              - End section
 *  @yield('name')           - Yield section
 *  @parent                  - Parent section
 *  @csrf                    - CSRF token field
 *  @method('PUT')           - Method spoofing
 *  @php($code)              - Raw PHP
 *  @dump($var)              - Dump variable
 *  @dd($var)                - Dump and die
 *  @auth                    - Check authentication
 *  @guest                   - Check guest
 *  @endauth/@endguest       - End auth/guest
 *  @can('ability')          - Check ability
 *  @endcan                  - End can
 *  @unless ($condition)     - Unless statement
 *  @endunless               - End unless
 *  @isset($var)             - Check if set
 *  @empty($var)             - Check if empty
 *  @endisset/@endempty      - End isset/empty
 *  @error('field')          - Error message
 *  @enderror                - End error
 *  @selected($condition)    - Selected attribute
 *  @checked($condition)     - Checked attribute
 */
class GSM
{
    /** @var string Cache directory for compiled templates */
    private string $cachePath;

    /** @var array<string, mixed> Template data */
    private array $data = [];

    /** @var array<string, string> Section contents */
    private array $sections = [];

    /** @var string|null Current extending layout */
    private ?string $extends = null;

    /** @var array<string, bool> Stack for nested sections */
    private array $sectionStack = [];

    public function __construct(string $cachePath = '')
    {
        $this->cachePath = $cachePath ?: sys_get_temp_dir() . '/gsm_cache';

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Set template data
     *
     * @param  array<string, mixed>  $data
     */
    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Render a GSM template
     *
     * @param  string  $template  Template path (without .gsm.php extension)
     * @param  array<string, mixed>  $data
     * @return string
     */
    public function render(string $template, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);

        $templateFile = $this->findTemplate($template);
        $compiledFile = $this->compile($templateFile);

        return $this->evaluate($compiledFile, $this->data);
    }

    /**
     * Find template file
     */
    private function findTemplate(string $template): string
    {
        // Try with .gsm.php extension
        $path = str_replace('.', '/', $template) . '.gsm.php';

        // Check common template directories
        $locations = [
            base_path('resources/views'),
            base_path('resources/views/layouts'),
            base_path('resources/views/partials'),
        ];

        foreach ($locations as $location) {
            $fullPath = $location . '/' . $path;
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        throw new \RuntimeException("Template not found: {$template}");
    }

    /**
     * Compile GSM template to PHP
     */
    private function compile(string $templateFile): string
    {
        $compiledFile = $this->cachePath . '/' . md5($templateFile) . '.php';

        // Recompile if template is newer than cached version
        if (!file_exists($compiledFile) || filemtime($templateFile) > filemtime($compiledFile)) {
            $content = file_get_contents($templateFile);
            $compiled = $this->compileString($content);
            file_put_contents($compiledFile, $compiled);
        }

        return $compiledFile;
    }

    /**
     * Compile GSM string to PHP
     */
    public function compileString(string $content): string
    {
        // Reset state
        $this->extends = null;
        $this->sections = [];
        $this->sectionStack = [];

        // Compile directives
        $patterns = [
            // Echo statements
            '/\{\{\s*(.+?)\s*\}\}/s' => '<?= htmlspecialchars($1, ENT_QUOTES, "UTF-8") ?>',
            '/\{\!\!\s*(.+?)\s*\!\!\}/s' => '<?= $1 ?>',

            // Conditionals
            '/@if\s*\((.+)\)/' => '<?php if ($1): ?>',
            '/@elseif\s*\((.+)\)/' => '<?php elseif ($1): ?>',
            '/@else/' => '<?php else: ?>',
            '/@endif/' => '<?php endif; ?>',

            // Unless
            '/@unless\s*\((.+)\)/' => '<?php if (!$1): ?>',
            '/@endunless/' => '<?php endif; ?>',

            // Loops
            '/@foreach\s*\((.+)\)/' => '<?php foreach ($1): ?>',
            '/@endforeach/' => '<?php endforeach; ?>',

            '/@for\s*\((.+)\)/' => '<?php for ($1): ?>',
            '/@endfor/' => '<?php endfor; ?>',

            '/@while\s*\((.+)\)/' => '<?php while ($1): ?>',
            '/@endwhile/' => '<?php endwhile; ?>',

            // Auth/Guest
            '/@auth/' => '<?php if (isset($_SESSION[\'user\'])): ?>',
            '/@endauth/' => '<?php endif; ?>',

            '/@guest/' => '<?php if (!isset($_SESSION[\'user\'])): ?>',
            '/@endguest/' => '<?php endif; ?>',

            // CSRF
            '/@csrf/' => '<input type="hidden" name="_token" value="<?= $_SESSION[\'_token\'] ?? \'\' ?>" />',

            // Method spoofing
            '/@method\([\'"](\w+)[\'"]\)/' => '<input type="hidden" name="_method" value="$1" />',

            // PHP
            '/@php\s*\((.+)\)/' => '<?php $1; ?>',

            // Dump
            '/@dump\s*\((.+)\)/' => '<?php var_dump($1); ?>',

            // Dump and die
            '/@dd\s*\((.+)\)/' => '<?php var_dump($1); exit; ?>',

            // Include
            '/@include\s*\([\'"](.+?)[\'"]\)/' => '<?php echo $this->render("$1", get_defined_vars()); ?>',

            // Extends
            '/@extends\s*\([\'"](.+?)[\'"]\)/' => '<?php $this->extends = "$1"; ?>',

            // Sections
            '/@section\s*\([\'"](.+?)[\'"]\)/' => '<?php $this->sectionStack[] = "$1"; ob_start(); ?>',
            '/@endsection/' => '<?php $this->sections[array_pop($this->sectionStack)] = ob_get_clean(); ?>',

            // Yield
            '/@yield\s*\([\'"](.+?)[\'"]\)/' => '<?= $this->sections["$1"] ?? "" ?>',

            // Parent
            '/@parent/' => '<?= $this->sections[$this->sectionStack[count($this->sectionStack) - 1]] ?? "" ?>',

            // Error
            '/@error\s*\([\'"](.+?)[\'"]\)/' => '<?php if (isset($errors["$1"])): ?>',
            '/@enderror/' => '<?php endif; ?>',

            // Selected/Checked helpers
            '/@selected\s*\((.+)\)/' => '<?= ($1) ? "selected" : "" ?>',
            '/@checked\s*\((.+)\)/' => '<?= ($1) ? "checked" : "" ?>',

            // Can/Ability
            '/@can\s*\([\'"](.+?)[\'"]\)/' => '<?php if (true): ?>', // Placeholder for ability check
            '/@endcan/' => '<?php endif; ?>',

            // IsSet/Empty
            '/@isset\s*\((.+?)\)/' => '<?php if (isset($1)): ?>',
            '/@endisset/' => '<?php endif; ?>',

            '/@empty\s*\((.+?)\)/' => '<?php if (empty($1)): ?>',
            '/@endempty/' => '<?php endif; ?>',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        // Handle component syntax @component / @endcomponent
        $content = preg_replace(
            '/@component\s*\([\'"](.+?)[\'"]\)/',
            '<?php ob_start(); ?>',
            $content
        );
        $content = preg_replace(
            '/@endcomponent/',
            '<?php $slot = ob_get_clean(); echo $this->render("$1", array_merge(get_defined_vars(), [\'slot\' => $slot])); ?>',
            $content
        );

        // Handle @php blocks
        $content = preg_replace(
            '/@php\s*\n/',
            '<?php ',
            $content
        );
        $content = preg_replace(
            '/@endphp\s*\n/',
            ' ?>',
            $content
        );

        return $content;
    }

    /**
     * Evaluate compiled template
     *
     * @param  array<string, mixed>  $data
     */
    private function evaluate(string $compiledFile, array $data): string
    {
        // Security: EXTR_SKIP prevents overwriting existing variables
        extract($data, EXTR_SKIP);

        // Security: Prevent arbitrary file inclusion
        if (!file_exists($compiledFile) || !is_readable($compiledFile)) {
            throw new \RuntimeException('Template file not found or not readable');
        }

        // Security: Path validation - ensure template is within allowed directory
        $realPath = realpath($compiledFile);
        $cachePath = realpath($this->cachePath);
        if ($realPath === false || $cachePath === false || strpos($realPath, $cachePath) !== 0) {
            throw new \RuntimeException('Invalid template path');
        }

        ob_start();
        try {
            include $compiledFile;
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw new \RuntimeException('Template rendering failed: ' . $e->getMessage(), 0, $e);
        }

        // Handle layout extends
        if ($this->extends) {
            $layoutContent = $this->render($this->extends, $data);
            $content = str_replace('@yield(\'content\')', $content, $layoutContent);
        }

        return $content;
    }

    /**
     * Define a section
     */
    public function section(string $name, string $content = ''): void
    {
        if ($content === '') {
            $this->sectionStack[] = $name;
            ob_start();
        } else {
            $this->sections[$name] = $content;
        }
    }

    /**
     * End a section
     */
    public function endSection(): void
    {
        if (!empty($this->sectionStack)) {
            $name = array_pop($this->sectionStack);
            $this->sections[$name] = ob_get_clean();
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $files = glob($this->cachePath . '/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Check if template exists
     */
    public function exists(string $template): bool
    {
        try {
            $this->findTemplate($template);
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Get base path helper
     */
    private function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__, 3);
        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }
}
