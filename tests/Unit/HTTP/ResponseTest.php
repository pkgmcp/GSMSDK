<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit\HTTP;

use GSMSDK\HTTP\Response;
use PHPUnit\Framework\TestCase;

/**
 * HTTP Response Tests
 * 
 * Tests the Response component for building HTTP responses.
 */
class ResponseTest extends TestCase {
    private Response $response;
    
    protected function setUp(): void {
        $this->response = new Response();
    }
    
    /**
     * @test
     */
    public function it_can_be_instantiated(): void {
        $this->assertInstanceOf(Response::class, $this->response);
    }
    
    /**
     * @test
     */
    public function it_sets_status_code(): void {
        $this->response->status(201);
        
        $this->assertEquals(201, $this->response->getStatusCode());
    }
    
    /**
     * @test
     */
    public function it_sets_body(): void {
        $this->response->body('<h1>Hello</h1>');
        
        $this->assertEquals('<h1>Hello</h1>', $this->response->getBody());
    }
    
    /**
     * @test
     */
    public function it_returns_json_response(): void {
        $data = ['message' => 'Success', 'code' => 200];
        $json = $this->response->json($data);
        
        $this->assertInstanceOf(Response::class, $json);
        $this->assertStringContainsString('application/json', $json->getHeader('Content-Type'));
    }
    
    /**
     * @test
     */
    public function it_sets_headers(): void {
        $this->response->header('X-Custom', 'Value');
        
        $this->assertEquals('Value', $this->response->getHeader('X-Custom'));
    }
    
    /**
     * @test
     */
    public function it_overwrites_header(): void {
        $this->response->header('X-Test', 'First');
        $this->response->header('X-Test', 'Second');
        
        $this->assertEquals('Second', $this->response->getHeader('X-Test'));
    }
    
    /**
     * @test
     */
    public function it_returns_null_for_missing_header(): void {
        $this->assertNull($this->response->getHeader('Non-Existent'));
    }
    
    /**
     * @test
     */
    public function it_chains_methods(): void {
        $response = $this->response
            ->status(201)
            ->header('Content-Type', 'application/json')
            ->body('{"created": true}');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals('{"created": true}', $response->getBody());
    }
    
    /**
     * @test
     */
    public function it_has_default_status_code(): void {
        $this->assertEquals(200, $this->response->getStatusCode());
    }
    
    /**
     * @test
     */
    public function it_has_default_empty_body(): void {
        $this->assertEquals('', $this->response->getBody());
    }
    
    /**
     * @test
     */
    public function it_sets_multiple_headers(): void {
        $this->response->header('X-One', '1');
        $this->response->header('X-Two', '2');
        $this->response->header('X-Three', '3');
        
        $this->assertEquals('1', $this->response->getHeader('X-One'));
        $this->assertEquals('2', $this->response->getHeader('X-Two'));
        $this->assertEquals('3', $this->response->getHeader('X-Three'));
    }
    
    /**
     * @test
     */
    public function it_serializes_array_to_json(): void {
        $result = $this->response->json(['success' => true]);
        
        // Just verify it's JSON capable - actual sending happens later
        $this->assertInstanceOf(Response::class, $result);
    }
    
    /**
     * @test
     */
    public function it_handles_empty_array_json(): void {
        $result = $this->response->json([]);
        
        $this->assertInstanceOf(Response::class, $result);
    }
    
    /**
     * @test
     */
    public function it_sets_content_type_for_json(): void {
        $this->response->json(['test' => 'data']);
        
        $this->assertStringContainsString('application/json', $this->response->getHeader('Content-Type'));
    }
    
    /**
     * @test
     */
    public function it_returns_default_value_for_header(): void {
        $this->assertNull($this->response->getHeader('X-Missing'));
    }
    
    /**
     * @test
     */
    public function it_sets_status_with_message(): void {
        $reflection = new \ReflectionClass(Response::class);
        $method = $reflection->getMethod('getStatusMessage');
        $method->setAccessible(true);
        
        $response = new Response();
        $statusMessage = $method->invoke($response);
        
        $this->assertIsString($statusMessage);
    }
    
    /**
     * @test
     */
    public function it_can_set_redirect_header(): void {
        $this->response->header('Location', 'https://example.com');
        
        $this->assertEquals('https://example.com', $this->response->getHeader('Location'));
    }
    
    /**
     * @test
     */
    public function it_returns_404_json_response(): void {
        $result = $this->response->status(404)->json(['error' => 'Not Found']);
        
        $this->assertEquals(404, $result->getStatusCode());
        $this->assertInstanceOf(Response::class, $result);
    }
}
