<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Contracts\RequestInterface;
use GSMSDK\Contracts\ResponseInterface;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;

/**
 * Base Controller for GSMSDK MVC components
 *
 * Provides standardized request/response handling and helper methods
 * for building RESTful APIs and web interfaces.
 */
abstract class Controller
{
    /** @var Application Framework container */
    protected Application $app;

    /** @var RequestInterface Current request */
    protected RequestInterface $request;

    /** @var ResponseInterface Response builder */
    protected ResponseInterface $response;

    /**
     * Initialize controller with application context
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Get current request instance
     */
    protected function request(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Get response builder
     */
    protected function response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Return JSON response
     *
     * @param  mixed  $data  Response data
     * @param  int  $status  HTTP status code
     */
    protected function json(mixed $data, int $status = 200): ResponseInterface
    {
        return $this->response
            ->status($status)
            ->header('Content-Type', 'application/json')
            ->body(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Return view response
     *
     * @param  string  $view  View name
     * @param  array<string, mixed>  $data  View data
     */
    protected function view(string $view, array $data = []): ResponseInterface
    {
        $viewPath = $this->app->config('paths.views', 'views') . "/{$view}.gsm.php";

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        return $this->response
            ->status(200)
            ->header('Content-Type', 'application/xhtml+xml')
            ->body($content);
    }

    /**
     * Redirect to URL
     *
     * @param  string  $url  Target URL
     * @param  int  $status  HTTP status code
     */
    protected function redirect(string $url, int $status = 302): ResponseInterface
    {
        return $this->response
            ->status($status)
            ->header('Location', $url);
    }

    /**
     * Validate request input
     *
     * @param  array<string, mixed>  $rules  Validation rules
     * @param  array<string, mixed>  $data  Input data (defaults to request body)
     * @return array<string, string>  Validation errors (empty if valid)
     */
    protected function validate(array $rules, ?array $data = null): array
    {
        $data = $data ?? $this->request->all();
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);

            foreach ($rulesArray as $rule) {
                $value = $data[$field] ?? null;

                if ($rule === 'required' && ($value === null || $value === '')) {
                    $errors[$field] = "The {$field} field is required.";
                    break;
                }

                if (str_starts_with($rule, 'min:') && mb_strlen($value) < (int) substr($rule, 4)) {
                    $errors[$field] = "The {$field} must be at least " . substr($rule, 4) . " characters.";
                    break;
                }

                if (str_starts_with($rule, 'max:') && mb_strlen($value) > (int) substr($rule, 4)) {
                    $errors[$field] = "The {$field} may not exceed " . substr($rule, 4) . " characters.";
                    break;
                }

                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "The {$field} must be a valid email address.";
                    break;
                }

                if ($rule === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = "The {$field} must be numeric.";
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Get configuration value
     */
    protected function config(string $key, mixed $default = null): mixed
    {
        return $this->app->config($key, $default);
    }

    /**
     * Make service from container
     *
     * @template T
     * @param  class-string<T>  $abstract  Service class
     * @return T Service instance
     */
    protected function make(string $abstract): mixed
    {
        return $this->app->make($abstract);
    }
}
