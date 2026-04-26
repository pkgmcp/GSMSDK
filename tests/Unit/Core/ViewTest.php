<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit\Core;

use GSMSDK\Core\Engine\GSM;
use GSMSDK\Core\View;
use PHPUnit\Framework\TestCase;

/**
 * View Component Tests
 * 
 * Tests the View renderer with GSM templating engine.
 */
class ViewTest extends TestCase {
    private View $view;
    private string $testViewPath;
    
    protected function setUp(): void {
        $engine = new GSM(
            $this->createMock(\GSMSDK\Core\Application::class),
            '/tmp/gsmsdk_cache'
        );
        
        $this->testViewPath = '/tmp/gsmsdk_test_views';
        if (!is_dir($this->testViewPath)) {
            mkdir($this->testViewPath, 0755, true);
        }
        
        $this->view = new View($engine, $this->testViewPath);
    }
    
    protected function tearDown(): void {
        // Clean up test files
        if (is_dir($this->testViewPath)) {
            array_map('unlink', glob($this->testViewPath . '/*'));
            rmdir($this->testViewPath);
        }
        
        if (is_dir('/tmp/gsmsdk_cache')) {
            array_map('unlink', glob('/tmp/gsmsdk_cache/*'));
        }
    }
    
    private function createTestView(string $name, string $content): void {
        file_put_contents($this->testViewPath . "/{$name}.gsm.php", $content);
    }
    
    /**
     * @test
     */
    public function it_can_be_instantiated(): void {
        $this->assertInstanceOf(View::class, $this->view);
    }
    
    /**
     * @test
     */
    public function it_renders_simple_view(): void {
        $this->createTestView('test', '<h1>Hello {{ $name }}</h1>');
        
        $output = $this->view->render('test', ['name' => 'World']);
        
        $this->assertStringContainsString('Hello World', $output);
    }
    
    /**
     * @test
     */
    public function it_escapes_output_by_default(): void {
        $this->createTestView('escape', '<div>{{ $html }}</div>');
        
        $output = $this->view->render('escape', ['html' => '<script>alert("xss")</script>']);
        
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }
    
    /**
     * @test
     */
    public function it_allows_raw_output(): void {
        $this->createTestView('raw', '<div>{!! $html !!}</div>');
        
        $output = $this->view->render('raw', ['html' => '<strong>Bold</strong>']);
        
        $this->assertStringContainsString('<strong>Bold</strong>', $output);
    }
    
    /**
     * @test
     */
    public function it_handles_if_statements(): void {
        $this->createTestView('if', '
@if($condition)
    <p>True</p>
@else
    <p>False</p>
@endif
');
        
        $output1 = $this->view->render('if', ['condition' => true]);
        $output2 = $this->view->render('if', ['condition' => false]);
        
        $this->assertStringContainsString('True', $output1);
        $this->assertStringContainsString('False', $output2);
    }
    
    /**
     * @test
     */
    public function it_handles_foreach_loop(): void {
        $this->createTestView('loop', '
@foreach($items as $item)
    <li>{{ $item }}</li>
@endforeach
');
        
        $output = $this->view->render('loop', ['items' => ['a', 'b', 'c']]);
        
        $this->assertStringContainsString('<li>a</li>', $output);
        $this->assertStringContainsString('<li>b</li>', $output);
        $this->assertStringContainsString('<li>c</li>', $output);
    }
    
    /**
     * @test
     */
    public function it_handles_extends(): void {
        $this->createTestView('layout', '
<html>
<body>@yield(\'content\')
</body>
</html>
');
        
        $this->createTestView('page', '
@extends(\'layout\')
@section(\'content\')
<h1>Page Content</h1>
@endsection
');
        
        $output = $this->view->render('page');
        
        $this->assertStringContainsString('<h1>Page Content</h1>', $output);
        $this->assertStringContainsString('<html>', $output);
    }
    
    /**
     * @test
     */
    public function it_handles_csrf_directive(): void {
        $this->createTestView('csrf', '<form>@csrf</form>');
        
        // Mock session
        $_SESSION['_token'] = 'test_csrf_token_123';
        
        $output = $this->view->render('csrf');
        
        $this->assertStringContainsString('name="_token"', $output);
        $this->assertStringContainsString('value="test_csrf_token_123"', $output);
    }
    
    /**
     * @test
     */
    public function it_throws_exception_for_missing_view(): void {
        $this->expectException(\RuntimeException::class);
        
        $this->view->render('nonexistent');
    }
}
