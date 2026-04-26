<?php

declare(strict_types=1);

namespace App\Controllers;

use GSMSDK\Core\Controller;

/**
 * GSM Template Engine Demo Controller
 */
class GsmDemoController extends Controller
{
    /**
     * Show GSM template demo
     */
    public function index($request, $response): void
    {
        $data = [
            'title' => 'GSM Template Engine Demo',
            'version' => $this->app->version(),
            'message' => 'Hello from GSM template!',
            'items' => ['Apple', 'Banana', 'Cherry', 'Date'],
            'features' => [
                'Blade-like syntax',
                'Template inheritance',
                'Components & slots',
                'Control structures',
                'CSRF protection',
                'Compiled caching',
            ],
        ];

        $html = $this->app->view('home', $data);
        
        $response->status(200)
                 ->header('Content-Type', 'text/html')
                 ->body($html)
                 ->send();
    }

    /**
     * Show raw GSM examples
     */
    public function examples($request, $response): void
    {
        $data = [
            'title' => 'GSM Syntax Examples',
            'examples' => [
                [
                    'name' => 'Echo',
                    'code' => '{{ $variable }}',
                    'desc' => 'Escape HTML output'
                ],
                [
                    'name' => 'Raw Echo',
                    'code' => '{!! $html !!}',
                    'desc' => 'Output raw HTML'
                ],
                [
                    'name' => 'If Statement',
                    'code' => '@if ($condition)\n  // code\n@endif',
                    'desc' => 'Conditional block'
                ],
                [
                    'name' => 'Foreach Loop',
                    'code' => '@foreach ($items as $item)\n  {{ $item }}\n@endforeach',
                    'desc' => 'Loop through items'
                ],
                [
                    'name' => 'Include',
                    'code' => '@include(\'partial.name\')',
                    'desc' => 'Include partial template'
                ],
                [
                    'name' => 'Extends',
                    'code' => '@extends(\'layouts.main\')\n@section(\'content\')\n  // content\n@endsection',
                    'desc' => 'Template inheritance'
                ],
                [
                    'name' => 'CSRF Token',
                    'code' => '@csrf',
                    'desc' => 'Generate CSRF token field'
                ],
            ],
        ];

        $html = $this->app->view('home', $data);
        
        $response->status(200)
                 ->header('Content-Type', 'text/html')
                 ->body($html)
                 ->send();
    }
}
