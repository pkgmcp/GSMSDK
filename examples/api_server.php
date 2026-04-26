<?php
/**
 * API Server Example
 */

use GSMSDK\Core\Application;
use GSMSDK\HTTP\Request;
use GSMSDK\HTTP\Response;
use GSMSDK\Database\Connection;
use GSMSDK\Database\QueryBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Initialize application
$app = new Application([
    'debug' => true,
    'environment' => 'development',
    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'gsmsdk',
        'username' => 'root',
        'password' => '',
    ],
]);

// Bind database connection
$app->bind('db', function ($app) {
    return new Connection($app->config('database'));
});

// Bind query builder
$app->bind('db.query', function ($app) {
    return new QueryBuilder($app->make('db'));
});

// Handle incoming request
$request = new Request();
$response = new Response();

// Simple routing
$method = $request->method();
$path = $request->path();

// CORS headers
$response->header('Access-Control-Allow-Origin', '*')
         ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
         ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

match (true) {
    $path === '/api/status' && $method === 'GET' => apiStatus($request, $response),
    $path === '/api/users' && $method === 'GET' => apiListUsers($request, $response, $app->make('db.query')),
    $path === '/api/users' && $method === 'POST' => apiCreateUser($request, $response, $app->make('db.query')),
    default => apiNotFound($response),
};

$response->send();

// API Handlers
function apiStatus(Request $request, Response $response): void
{
    $response->json([
        'status' => 'ok',
        'service' => 'GSMSDK API',
        'version' => '1.0.0',
        'timestamp' => date('c'),
    ]);
}

function apiListUsers(Request $request, Response $response, QueryBuilder $db): void
{
    try {
        $users = $db->table('users')->get();
        $response->json(['success' => true, 'data' => $users]);
    } catch (\Throwable $e) {
        $response->status(500)->json(['success' => false, 'error' => $e->getMessage()]);
    }
}

function apiCreateUser(Request $request, Response $response, QueryBuilder $db): void
{
    $errors = validate($request->all(), [
        'name' => 'required|min:2',
        'email' => 'required|email',
    ], $request->app());

    if (!empty($errors)) {
        $response->status(422)->json(['success' => false, 'errors' => $errors]);
        return;
    }

    try {
        $id = $db->table('users')->insert($request->all());
        $response->status(201)->json(['success' => true, 'id' => $id]);
    } catch (\Throwable $e) {
        $response->status(500)->json(['success' => false, 'error' => $e->getMessage()]);
    }
}

function apiNotFound(Response $response): void
{
    $response->status(404)->json(['success' => false, 'error' => 'Endpoint not found']);
}

function validate(array $data, array $rules, $app): array
{
    $errors = [];

    foreach ($rules as $field => $ruleSet) {
        $rulesArray = explode('|', $ruleSet);

        foreach ($rulesArray as $rule) {
            $value = $data[$field] ?? null;

            if ($rule === 'required' && ($value === null || $value === '')) {
                $errors[$field] = "The {$field} field is required.";
                break;
            }

            if (str_starts_with($rule, 'min:') && strlen($value) < (int) substr($rule, 4)) {
                $errors[$field] = "The {$field} must be at least " . substr($rule, 4) . " characters.";
                break;
            }

            if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "The {$field} must be a valid email address.";
                break;
            }
        }
    }

    return $errors;
}
