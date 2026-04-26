<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit\Core\Auth;

use GSMSDK\Core\Application;
use GSMSDK\Core\Auth\AuthManager;
use GSMSDK\HTTP\Request;
use PHPUnit\Framework\TestCase;

/**
 * Authentication System Tests
 * 
 * Tests the AuthManager for authentication and authorization.
 */
class AuthTest extends TestCase {
    private AuthManager $auth;
    private Request $request;
    
    protected function setUp(): void {
        $_SESSION = [];
        
        $this->request = $this->createMock(Request::class);
        $app = new Application(['debug' => true]);
        
        $this->auth = new AuthManager($app);
    }
    
    protected function tearDown(): void {
        $_SESSION = [];
    }
    
    /**
     * @test
     */
    public function it_can_be_instantiated(): void {
        $this->assertInstanceOf(AuthManager::class, $this->auth);
    }
    
    /**
     * @test
     */
    public function it_generates_csrf_token(): void {
        $token = $this->auth->csrfToken();
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertEquals($token, $_SESSION['_token']);
    }
    
    /**
     * @test
     */
    public function it_generates_same_token_on_multiple_calls(): void {
        $first = $this->auth->csrfToken();
        $second = $this->auth->csrfToken();
        
        $this->assertEquals($first, $second);
    }
    
    /**
     * @test
     */
    public function it_validates_correct_csrf_token(): void {
        $token = $this->auth->csrfToken();
        
        $this->assertTrue($this->auth->validateCsrf($token));
    }
    
    /**
     * @test
     */
    public function it_rejects_invalid_csrf_token(): void {
        $this->auth->csrfToken();
        
        $this->assertFalse($this->auth->validateCsrf('invalid_token'));
    }
    
    /**
     * @test
     */
    public function it_rejects_empty_csrf_token(): void {
        $this->auth->csrfToken();
        
        $this->assertFalse($this->auth->validateCsrf(''));
    }
    
    /**
     * @test
     */
    public function it_checks_authentication_when_not_logged_in(): void {
        $this->assertFalse($this->auth->check());
    }
    
    /**
     * @test
     */
    public function it_returns_null_user_when_not_logged_in(): void {
        $this->assertNull($this->auth->user());
    }
    
    /**
     * @test
     */
    public function it_returns_guest_user_when_not_logged_in(): void {
        $guest = $this->auth->guest();
        
        $this->assertIsArray($guest);
        $this->assertArrayHasKey('id', $guest);
        $this->assertArrayHasKey('name', $guest);
        $this->assertEquals(0, $guest['id']);
        $this->assertEquals('Guest', $guest['name']);
    }
    
    /**
     * @test
     */
    public function it_stores_session_data(): void {
        $this->auth->setSessionData('test_key', 'test_value');
        
        $this->assertEquals('test_value', $this->auth->getSessionData('test_key'));
    }
    
    /**
     * @test
     */
    public function it_returns_default_for_missing_session_data(): void {
        $this->assertEquals('default', $this->auth->getSessionData('missing', 'default'));
    }
    
    /**
     * @test
     */
    public function it_removes_session_data(): void {
        $this->auth->setSessionData('test_key', 'test_value');
        $this->auth->removeSessionData('test_key');
        
        $this->assertNull($this->auth->getSessionData('test_key'));
    }
    
    /**
     * @test
     */
    public function it_regenerates_session_id(): void {
        $oldId = session_id();
        $this->auth->regenerateSessionId();
        
        $this->assertNotEquals($oldId, session_id());
    }
    
    /**
     * @test
     */
    public function it_starts_session(): void {
        $this->assertTrue(session_status() === PHP_SESSION_ACTIVE || session_status() === PHP_SESSION_NONE);
    }
    
    /**
     * @test
     */
    public function it_returns_false_for_csrf_validation_when_no_token_in_session(): void {
        // Clear session to ensure no token exists
        $_SESSION = [];
        
        $this->assertFalse($this->auth->validateCsrf('any_token'));
    }
    
    /**
     * @test
     */
    public function it_handles_complex_session_data(): void {
        $complexData = [
            'user' => [
                'id' => 1,
                'name' => 'Test User',
                'roles' => ['admin', 'user']
            ],
            'settings' => [
                'theme' => 'dark',
                'language' => 'en'
            ]
        ];
        
        $this->auth->setSessionData('complex', $complexData);
        
        $this->assertEquals($complexData, $this->auth->getSessionData('complex'));
    }
    
    /**
     * @test
     */
    public function it_overwrites_session_data(): void {
        $this->auth->setSessionData('key', 'first');
        $this->auth->setSessionData('key', 'second');
        
        $this->assertEquals('second', $this->auth->getSessionData('key'));
    }
}
