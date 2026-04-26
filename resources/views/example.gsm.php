@extends('layouts/main')

@section('content')
<h2>📄 GSM Template Example</h2>
<p>This demonstrates the GSM templating engine syntax (similar to Blade).</p>

<div class="card">
  <div class="card-header">
    <h3>Echo Statements</h3>
  </div>
  <div style="padding:1rem 0">
    <p>Escaped: {{ $message ?? 'Hello World' }}</p>
    <p>Raw: {!! '<strong>Bold Text</strong>' !!}</p>
    <p>PHP: @php($x = 5 + 3) Result: {{ $x }}</p>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>Control Structures</h3>
  </div>
  
  <h4>If/Else</h4>
  @if (true)
    <p class="ok">✓ This is true</p>
  @else
    <p class="err">✗ This is false</p>
  @endif

  <h4>Unless</h4>
  @unless (false)
    <p class="ok">✓ Unless condition is true</p>
  @endunless

  <h4>Foreach</h4>
  @foreach ($items ?? [1, 2, 3, 4, 5] as $item)
    <span class="badge">{{ $item }}</span>
  @endforeach

  <h4>For</h4>
  @for ($i = 0; $i < 3; $i++)
    <span class="badge badge-green">{{ $i }}</span>
  @endfor
</div>

<div class="card">
  <div class="card-header">
    <h3>Components & Includes</h3>
  </div>
  
  <p>Including partials:</p>
  @include('partials/example')
  
  <p>Using slots:</p>
  @component('components.card')
    This is the slot content!
  @endcomponent
</div>

<div class="card">
  <div class="card-header">
    <h3>Forms & Helpers</h3>
  </div>
  
  <form method="POST">
    @csrf
    <input type="text" placeholder="CSRF token included" style="margin:0.5rem 0">
    
    <button type="submit" class="btn">Submit</button>
    <button type="submit" formmethod="POST" @method('PUT') class="btn btn-secondary">Update</button>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <h3>Auth & Session</h3>
  </div>
  
  @auth
    <p class="ok">✓ User is authenticated</p>
  @endauth
  
  @guest
    <p class="info">ℹ User is a guest</p>
  @endguest
</div>
@endsection
