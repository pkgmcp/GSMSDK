<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit\HTTP;

use GSMSDK\HTTP\Request;
use PHPUnit\Framework\TestCase;

/**
 * HTTP Request Tests
 * 
 * Tests the Request component for HTTP data handling.
 */
class RequestTest extends TestCase {
    private Request $request;
    
    protected function setUp(): void {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'TestAgent/1.0',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.1',
        ];
        $_GET = ['page' => 'home', 'limit' => '10'];
        $_POST = ['name' => 'Test User', 'email' => 'test@example.com'];
        $_COOKIE = ['session' => 'abc123'];
        $_FILES = [];
        
        $this->request = new Request();
    }
    
    protected function tearDown(): void {
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_FILES = [];
    }
    
    /**
     * @test
     */
    public function it_can_get_query_parameter(): void {
        $this->assertEquals('home', $this->request->get('page'));
        $this->assertEquals('10', $this->request->get('limit'));
    }
    
    /**
     * @test
     */
    public function it_returns_default_value(): void {
        $this->assertEquals('default', $this->request->get('nonexistent', 'default'));
    }
    
    /**
     * @test
     */
    public function it_can_get_post_parameter(): void {
        $this->assertEquals('Test User', $this->request->post('name'));
        $this->assertEquals('test@example.com', $this->request->post('email'));
    }
    
    /**
     * @test
     */
    public function it_can_get_all_post_data(): void {
        $all = $this->request->all();
        
        $this->assertArrayHasKey('name', $all);
        $this->assertArrayHasKey('email', $all);
        $this->assertEquals('Test User', $all['name']);
    }
    
    /**
     * @test
     */
    public function it_can_get_cookie(): void {
        $this->assertEquals('abc123', $this->request->cookie('session'));
    }
    
    /**
     * @test
     */
    public function it_can_get_server_variable(): void {
        $this->assertEquals('127.0.0.1', $this->request->server('REMOTE_ADDR'));
        $this->assertEquals('TestAgent/1.0', $this->request->server('HTTP_USER_AGENT'));
    }
    
    /**
     * @test
     */
    public function it_gets_request_method(): void {
        $this->assertEquals('GET', $this->request->getMethod());
    }
    
    /**
     * @test
     */
    public function it_identifies_post_request(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        
        $this->assertTrue($request->isPost());
        $this->assertFalse($request->isGet());
    }
    
    /**
     * @test
     */
    public function it_identifies_get_request(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        
        $this->assertTrue($request->isGet());
        $this->assertFalse($request->isPost());
    }
    
    /**
     * @test
     */
    public function it_identifies_ajax_request(): void {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = new Request();
        
        $this->assertTrue($request->isAjax());
    }
    
    /**
     * @test
     */
    public function it_gets_client_ip(): void {
        $this->assertEquals('127.0.0.1', $this->request->getClientIP());
    }
    
    /**
     * @test
     */
    public function it_gets_user_agent(): void {
        $this->assertEquals('TestAgent/1.0', $this->request->getUserAgent());
    }
    
    /**
     * @test
     */
    public function it_has_csrf_token(): void {
        $_SESSION['_token'] = 'test_token_456';
        
        $token = $this->request->csrfToken();
        
        $this->assertEquals('test_token_456', $token);
    }
    
    /**
     * @test
     */
    public function it_validates_csrf_token(): void {
        $_SESSION['_token'] = 'valid_token';
        
        $this->assertTrue($this->request->validateCsrf('valid_token'));
        $this->assertFalse($this->request->validateCsrf('invalid_token'));
    }
    
    /**
     * @test
     */
    public function it_handles_missing_query_param(): void {
        $this->assertNull($this->request->get('missing'));
        $this->assertEquals('fallback', $this->request->get('missing', 'fallback'));
    }
    
    /**
     * @test
     */
    public function it_handles_empty_request(): void {
        $_GET = [];
        $_POST = [];
        
        $request = new Request();
        
        $this->assertEmpty($request->all());
        $this->assertNull($request->get('anything'));
    }
    
    /**
     * @test
     */
    public function it_returns_false_for_is_ajax_when_not_set(): void {
        $this->assertFalse($this->request->isAjax());
    }
    
    /**
     * @test
     */
    public function it_returns_null_for_missing_server_var(): void {
        $this->assertNull($this->request->server('DOES_NOT_EXIST'));
    }
    
    /**
     * @test
     */
    public function it_returns_null_for_missing_cookie(): void {
        $this->assertNull($this->request->cookie('missing_cookie'));
    }
}
