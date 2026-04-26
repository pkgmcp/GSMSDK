@extends('layouts/main')

@section('content')
<h1>🌐 API Explorer</h1>
<p>Browse and test all available API endpoints interactively.</p>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <span>🔍</span> Quick Search
    </h3>
  </div>
  
  <div class="field">
    <label class="field-label">Search endpoints...</label>
    <input type="text" 
           class="field-input mono" 
           id="apiSearch" 
           placeholder="Type to search endpoints..."
           oninput="filterEndpoints(this.value)"/>
  </div>
  
  <div style="margin-top:1rem" id="searchResults">
    <p style="color:var(--text3)">Type to search {{ count($endpoints) }} endpoints...</p>
  </div>
</div>

@foreach ($endpoints as $tag => $group)
<div class="card animate-in" style="animation-delay:{{ loop.index * 0.05 }}s">
  <div class="card-header">
    <h3 class="card-title">
      <span>{{ match($tag) {
        'devices' => '📱',
        'flash' => '⚡',
        'auth' => '🔐',
        default => '⚙️'
      } }}</span>
      {{ $tag }}
    </h3>
    <span class="badge badge-info">{{ count($group) }} endpoints</span>
  </div>
  
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="width:80px">Method</th>
          <th>Endpoint</th>
          <th>Description</th>
          <th style="width:100px">Auth</th>
          <th style="width:100px">Cache</th>
          <th style="width:100px">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($group as $endpoint)
        <tr class="endpoint-row" data-method="{{ $endpoint['method'] }}" data-path="{{ $endpoint['path'] }}">
          <td>
            <span class="badge 
              @if($endpoint['method'] === 'GET') badge-info
              @elseif($endpoint['method'] === 'POST') badge-success
              @elseif($endpoint['method'] === 'PUT/PATCH') badge-warning
              @elseif($endpoint['method'] === 'DELETE') badge-danger
              @else badge-accent
              @endif">
              {{ $endpoint['method'] }}
            </span>
          </td>
          <td class="mono">{{ $endpoint['path'] }}</td>
          <td>{{ $endpoint['summary'] }}</td>
          <td>
            @if($endpoint['auth'])
              <span class="badge badge-success">✓</span>
            @else
              <span class="badge">-</span>
            @endif
          </td>
          <td>
            @if($endpoint['cacheable'])
              <span class="badge badge-info">✓</span>
            @else
              <span class="badge">-</span>
            @endif
          </td>
          <td>
            <button class="btn btn-secondary" 
                    style="font-size:0.7rem;padding:0.2rem 0.4rem"
                    onclick="testEndpoint('{{ $endpoint['method'] }}', '{{ $endpoint['path'] }}')">
              Test
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endforeach

<!-- Test Modal -->
<div class="modal-overlay" id="testModal">
  <div class="modal">
    <h3 class="modal-title">Test Endpoint</h3>
    
    <div style="margin:1rem 0">
      <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem">
        <span class="badge badge-info" id="modalMethod">GET</span>
        <span class="mono" id="modalPath">/api/endpoint</span>
      </div>
      
      <div class="field" id="modalAuthSection" style="display:none">
        <label class="field-label">Authentication</label>
        <select class="field-select" id="authType">
          <option value="none">None</option>
          <option value="token">Bearer Token</option>
          <option value="apiKey">API Key</option>
        </select>
        <input type="text" class="field-input mono" id="authToken" 
               placeholder="Token or API Key" style="display:none;margin-top:0.5rem"/>
      </div>
      
      <div class="field" id="modalParams" style="display:none">
        <label class="field-label">Query Parameters</label>
        <textarea class="field-input mono" id="queryParams" 
                  placeholder="{\"param\": \"value\"}" 
                  style="font-size:0.8rem;height:80px;display:none"></textarea>
      </div>
      
      <div id="modalResponse" style="display:none">
        <label class="field-label">Response</label>
        <div class="output-wrap">
          <div class="output-body output-cmd" id="responseContent" 
               style="max-height:200px;font-size:0.75rem"></div>
        </div>
      </div>
    </div>
    
    <div style="display:flex;gap:0.5rem;justify-content:flex-end">
      <button class="btn btn-secondary" onclick="closeModal()">Close</button>
      <button class="btn btn-primary" id="sendRequestBtn" onclick="sendRequest()">
        Send Request
      </button>
    </div>
  </div>
</div>

<script>
let currentEndpoint = null;

function filterEndpoints(query) {
  const rows = document.querySelectorAll('.endpoint-row');
  const resultsDiv = document.getElementById('searchResults');
  
  if (!query) {
    resultsDiv.style.display = 'none';
    rows.forEach(row => row.style.display = '');
    return;
  }
  
  resultsDiv.style.display = 'block';
  
  let visibleCount = 0;
  query = query.toLowerCase();
  
  rows.forEach(row => {
    const text = (row.getAttribute('data-method') + ' ' + row.getAttribute('data-path')).toLowerCase();
    if (text.includes(query)) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  resultsDiv.innerHTML = `<p>Found ${visibleCount} matching endpoints</p>`;
}

function testEndpoint(method, path) {
  currentEndpoint = { method, path };
  
  document.getElementById('modalMethod').textContent = method;
  document.getElementById('modalPath').textContent = path;
  
  document.getElementById('testModal').classList.add('show');
  
  // Check if auth is required (simplified check)
  const authSection = document.getElementById('modalAuthSection');
  authSection.style.display = path.includes('auth') ? 'block' : 'none';
}

function closeModal() {
  document.getElementById('testModal').classList.remove('show');
  document.getElementById('modalResponse').style.display = 'none';
}

function sendRequest() {
  if (!currentEndpoint) return;
  
  const btn = document.getElementById('sendRequestBtn');
  const originalText = btn.textContent;
  btn.innerHTML = '<span class="spinner sm"></span> Sending...';
  btn.disabled = true;
  
  const responseDiv = document.getElementById('modalResponse');
  const responseContent = document.getElementById('responseContent');
  
  // Simulate API call (in production, this would fetch the actual endpoint)
  setTimeout(() => {
    responseContent.innerHTML = `<span class="output-info">{
  "status": "success",
  "endpoint": "${currentEndpoint.method} ${currentEndpoint.path}",
  "message": "Response received successfully"
}</span>`;
    
    responseDiv.style.display = 'block';
    btn.innerHTML = originalText;
    btn.disabled = false;
  }, 500);
}

// Close modal on outside click
document.getElementById('testModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
@endsection
