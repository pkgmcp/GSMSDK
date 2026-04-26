<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit\Core;

use GSMSDK\Core\Application;
use PHPUnit\Framework\TestCase;

/**
 * Application Core Tests
 * 
 * Tests the Application container, service binding, and resolution.
 */
class ApplicationTest extends TestCase {
    private Application $app;
    
    protected function setUp(): void {
        $this->app = new Application([
            'debug' => true,
            'environment' => 'testing'
        ]);
    }
    
    /**
     * @test
     */
    public function it_can_be_instantiated(): void {
        $this->assertInstanceOf(Application::class, $this->app);
    }
    
    /**
     * @test
     */
    public function it_has_config(): void {
        $this->assertTrue($this->app->getConfig('debug'));
        $this->assertEquals('testing', $this->app->getConfig('environment'));
        $this->assertNull($this->app->getConfig('nonexistent'));
    }
    
    /**
     * @test
     */
    public function it_binds_and_resolves_service(): void {
        $this->app->bind('test.service', function () {
            return new \stdClass();
        });
        
        $service = $this->app->make('test.service');
        $this->assertInstanceOf(\stdClass::class, $service);
    }
    
    /**
     * @test
     */
    public function it_resolves_singleton(): void {
        $this->app->singleton('test.singleton', function () {
            return uniqid();
        });
        
        $first = $this->app->make('test.singleton');
        $second = $this->app->make('test.singleton');
        
        $this->assertEquals($first, $second);
    }
    
    /**
     * @test
     */
    public function it_checks_existing_binding(): void {
        $this->app->bind('test.service', function () {
            return new \stdClass();
        });
        
        $this->assertTrue($this->app->bound('test.service'));
        $this->assertFalse($this->app->bound('nonexistent.service'));
    }
    
    /**
     * @test
     */
    public function it_resolves_class_dependency(): void {
        $this->app->bind(\stdClass::class, function () {
            return new \stdClass();
        });
        
        $instance = $this->app->make(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $instance);
    }
    
    /**
     * @test
     */
    public function it_has_default_config(): void {
        $app = new Application();
        
        $this->assertIsArray($app->getConfig());
        $this->assertFalse($app->getConfig('debug'));
        $this->assertEquals('production', $app->getConfig('environment'));
    }
}
