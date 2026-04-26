<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit;

use GSMSDK\Core\MvcApplication;
use PHPUnit\Framework\TestCase;

/**
 * @covers \GSMSDK\Core\MvcApplication
 */
class MvcApplicationTest extends TestCase
{
    private MvcApplication $app;

    protected function setUp(): void
    {
        $this->app = new MvcApplication([
            'debug' => true,
            'environment' => 'testing',
            'paths' => [
                'base' => dirname(__DIR__, 2),
                'views' => dirname(__DIR__, 2) . '/resources/views',
                'controllers' => dirname(__DIR__, 2) . '/app/Controllers',
                'models' => dirname(__DIR__, 2) . '/app/Models',
            ],
        ]);
    }

    public function testApplicationCanBeCreated(): void
    {
        $this->assertInstanceOf(MvcApplication::class, $this->app);
    }

    public function testApplicationVersion(): void
    {
        $this->assertSame('1.0.0', $this->app->version());
    }

    public function testApplicationEnvironment(): void
    {
        $this->assertSame('testing', $this->app->environment());
    }

    public function testRouteRegistration(): void
    {
        $this->app->get('/test', fn($r, $s) => null);
        $this->app->post('/test', fn($r, $s) => null);
        $this->app->put('/test', fn($r, $s) => null);
        $this->app->delete('/test', fn($r, $s) => null);

        $this->assertTrue(true); // No exception means success
    }

    public function testControllerNamespaceCanBeSet(): void
    {
        $this->app->setNamespace('Test\\Controllers');
        $this->assertTrue(true);
    }

    public function testViewRendering(): void
    {
        $content = $this->app->view('home', ['title' => 'Test']);
        $this->assertIsString($content);
        $this->assertStringContainsString('GSMSDK', $content);
    }

    public function testViewRenderingWithLayout(): void
    {
        $content = $this->app->render('home', ['title' => 'Test'], 'thegridcn');
        $this->assertIsString($content);
        $this->assertStringContainsString('<!DOCTYPE html>', $content);
    }

    public function testAssetUrlGeneration(): void
    {
        $url = $this->app->asset('css/style.css');
        $this->assertStringStartsWith('/', $url);
        $this->assertStringContainsString('css/style.css', $url);
    }
}
