<?php

declare(strict_types=1);

namespace GSMSDK\Core\Auth;

use GSMSDK\HTTP\Request;
use GSMSDK\Core\Application;
use GSMSDK\Core\Controller;

/**
 * AI-Enhanced Authentication & Authorization Manager
 *
 * Provides session-based, token-based, and API key authentication
 * with built-in CSRF protection and rate limiting.
 */
class AuthManager
{
    /** @var Application */
    private Application $app;

    /** @var array<string, mixed> User data */
    private array $user = [];

    /** @var bool Whether authenticated */
    private bool $authenticated = false;

    /** @var array Rate limit tracking */
    private array $rateLimit = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->initSession();
    }

    /**
     * Initialize secure session
     */
    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $params = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => $params['lifetime'],
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }

        // Generate CSRF token
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        // Load user from session
        if (!empty($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
            $this->authenticated = true;
        }
    }

    /**
     * Attempt authentication with credentials
     */
    public function attempt(array $credentials): bool
    {
        $user = $this->findUser($credentials);

        if ($user && $this->validateCredentials($user, $credentials)) {
            $this->login($user);
            return true;
        }

        return false;
    }

    /**
     * Find user by credentials
     */
    private function findUser(array $credentials): ?array
    {
        // In production, query your database here
        // Example: SELECT * FROM users WHERE email = ?

        // Demo user for testing
        if (($credentials['email'] ?? '') === 'admin@example.com' &&
            ($credentials['password'] ?? '') === 'secret') {
            return [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'permissions' => ['*']
            ];
        }

        return null;
    }

    /**
     * Validate user credentials (with password hashing)
     */
    private function validateCredentials(array $user, array $credentials): bool
    {
        if (isset($credentials['password'])) {
            // In production: return password_verify($credentials['password'], $user['password_hash']);
            return $credentials['password'] === 'secret'; // Demo only
        }

        return false;
    }

    /**
     * Login user and create session
     */
    public function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $this->user = $user;
        $this->authenticated = true;

        $this->logActivity('login', $user['id'] ?? null);
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $this->logActivity('logout', $this->user['id'] ?? null);

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        $this->user = [];
        $this->authenticated = false;
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return $this->authenticated;
    }

    /**
     * Check if user is guest (not authenticated)
     */
    public function guest(): bool
    {
        return !$this->authenticated;
    }

    /**
     * Get current user
     */
    public function user(): ?array
    {
        return $this->authenticated ? $this->user : null;
    }

    /**
     * Get user ID
     */
    public function id(): ?int
    {
        return $this->user['id'] ?? null;
    }

    /**
     * Check user role
     */
    public function hasRole(string $role): bool
    {
        return ($this->user['role'] ?? '') === $role;
    }

    /**
     * Check user permission
     */
    public function can(string $permission): bool
    {
        $permissions = $this->user['permissions'] ?? [];
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrf(string $token): bool
    {
        return hash_equals($_SESSION['_token'] ?? '', $token);
    }

    /**
     * Get CSRF token
     */
    public function csrfToken(): string
    {
        return $_SESSION['_token'] ?? '';
    }

    /**
     * CSRF token middleware
     */
    public function csrfMiddleware(Request $request): ?\GSMSDK\HTTP\Response
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->post('_token') ?? $request->header('X-CSRF-Token');

            if (!$this->validateCsrf($token ?? '')) {
                return (new \GSMSDK\HTTP\Response())
                    ->status(419)
                    ->json(['error' => 'CSRF token mismatch']);
            }
        }
        return null;
    }

    /**
     * Rate limiting middleware
     */
    public function rateLimitMiddleware(Request $request, string $key, int $max = 60, int $window = 60): ?\GSMSDK\HTTP\Response
    {
        $ip = $request->server('REMOTE_ADDR', 'unknown');
        $rateKey = "{$key}:{$ip}";
        $now = time();

        if (!isset($this->rateLimit[$rateKey])) {
            $this->rateLimit[$rateKey] = ['count' => 0, 'window' => $now];
        }

        // Reset window if expired
        if ($now - $this->rateLimit[$rateKey]['window'] > $window) {
            $this->rateLimit[$rateKey] = ['count' => 0, 'window' => $now];
        }

        $this->rateLimit[$rateKey]['count']++;

        if ($this->rateLimit[$rateKey]['count'] > $max) {
            return (new \GSMSDK\HTTP\Response())
                ->status(429)
                ->json(['error' => 'Rate limit exceeded']);
        }

        return null;
    }

    /**
     * API token authentication
     */
    public function authenticateWithToken(string $token): bool
    {
        // In production: query database for API token
        // Example: SELECT * FROM api_tokens WHERE token = ? AND active = 1

        if ($token === 'demo-api-token') {
            $this->user = [
                'id' => 999,
                'name' => 'API Client',
                'email' => 'api@example.com',
                'role' => 'api',
                'permissions' => ['api.read', 'api.write']
            ];
            $this->authenticated = true;
            return true;
        }

        return false;
    }

    /**
     * Generate API token
     */
    public function generateApiToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Log activity
     */
    private function logActivity(string $action, ?int $userId): void
    {
        // In production: insert into activity_log table
        error_log(sprintf(
            '[Auth] %s: user_id=%s, ip=%s, time=%s',
            $action,
            $userId ?? 'null',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            date('Y-m-d H:i:s')
        ));
    }

    /**
     * Auth middleware for routes
     */
    public function authMiddleware(Request $request): ?\GSMSDK\HTTP\Response
    {
        if (!$this->check()) {
            if ($request->expectsJson()) {
                return (new \GSMSDK\HTTP\Response())
                    ->status(401)
                    ->json(['error' => 'Unauthorized']);
            }

            return (new \GSMSDK\HTTP\Response())
                ->status(302)
                ->redirect('/login');
        }
        return null;
    }

    /**
     * Role middleware
     */
    public function roleMiddleware(string $role): callable
    {
        return function (Request $request) use ($role): ?\GSMSDK\HTTP\Response {
            if (!$this->hasRole($role)) {
                if ($request->expectsJson()) {
                    return (new \GSMSDK\HTTP\Response())
                        ->status(403)
                        ->json(['error' => 'Forbidden: Insufficient role']);
                }
                return (new \GSMSDK\HTTP\Response())
                    ->status(403)
                    ->body('<h1>403 - Forbidden</h1>');
            }
            return null;
        };
    }

    /**
     * Permission middleware
     */
    public function permissionMiddleware(string $permission): callable
    {
        return function (Request $request) use ($permission): ?\GSMSDK\HTTP\Response {
            if (!$this->can($permission)) {
                return (new \GSMSDK\HTTP\Response())
                    ->status(403)
                    ->json(['error' => 'Forbidden: Insufficient permissions']);
            }
            return null;
        };
    }
}
