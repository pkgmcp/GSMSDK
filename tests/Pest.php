<?php

/**
 * Pest Configuration for GSMSDK
 * 
 * This file configures Pest-style testing for GSMSDK.
 * Provides beforeEach hooks, dataset definitions, and test utilities.
 */

use GSMSDK\Core\Application;
use GSMSDK\Core\Auth\AuthManager;
use GSMSDK\HTTP\Request;
use GSMSDK\Database\Connection;
use GSMSDK\Database\QueryBuilder;

/**
 * Before each test - setup application instance
 */
beforeEach(function () {
    $this->app = new Application([
        'debug' => true,
        'environment' => 'testing',
        'database' => [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]
    ]);
});

/**
 * Before each auth test - setup auth manager
 */
beforeEach(function () {
    $this->auth = new AuthManager($this->app);
});

/**
 * Before each request test - setup request mock
 */
beforeEach(function () {
    $_SERVER = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_USER_AGENT' => 'Pest/1.0'
    ];
    $_GET = [];
    $_POST = [];
    $_COOKIE = [];
    $_SESSION = [];
    $_FILES = [];
    
    $this->request = new Request();
});

/**
 * Before each database test - setup in-memory database
 */
beforeEach(function () {
    $this->connection = new Connection([
        'driver' => 'sqlite',
        'database' => ':memory:'
    ]);
    
    $this->db = new QueryBuilder($this->connection);
    
    // Create users table
    $pdo = $this->getPDO();
    $pdo->exec('CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    $pdo->exec("INSERT INTO users (name, email) VALUES 
        ('Alice', 'alice@example.com'),
        ('Bob', 'bob@example.com')
    ");
});

/**
 * Helper: Get PDO instance from connection
 */
function getPDO() {
    global $connection;
    $reflection = new \ReflectionClass($connection);
    $method = $reflection->getMethod('getPdo');
    $method->setAccessible(true);
    return $method->invoke($connection);
}

/**
 * Helper: Create test user
 */
function createUser($name, $email) {
    global $db;
    return $db->table('users')->insert([
        'name' => $name,
        'email' => $email
    ]);
}

/**
 * Helper: Assert JSON response structure
 */
function assertJsonStructure($response, array $structure) {
    $data = json_decode($response, true);
    
    foreach ($structure as $key => $type) {
        if (is_array($type)) {
            assertArrayHasKey($key, $data);
            assertJsonStructure($data[$key], $type);
        } else {
            assertArrayHasKey($key, $data);
            assertTrue(is_$type($data[$key]));
        }
    }
}
