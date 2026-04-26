@extends('layouts/main')

@section('content')
<h2>🏠 Overview</h2>
<p>GSMSDK - Full-Stack PHP Framework with ADB & Fastboot Integration</p>

<div class="grid">
  <div class="card">
    <div class="card-header">
      <h3>Framework Information</h3>
    </div>
    <div style="padding:0.5rem 0">
      <div style="display:flex;justify-content:space-between;padding:0.25rem 0">
        <span style="color:var(--text3)">Version</span>
        <span class="text-green-400">{{ $version ?? '1.0.0' }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:0.25rem 0">
        <span style="color:var(--text3)">Environment</span>
        <span class="text-green-400">{{ $environment ?? 'production' }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:0.25rem 0">
        <span style="color:var(--text3)">PHP Version</span>
        <span>{{ PHP_VERSION }}</span>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3>Quick Actions</h3>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.5rem">
      <button class="btn" onclick="checkServer()">🔍 Check Server</button>
      <button class="btn btn-secondary" onclick="listDevices()">📱 List Devices</button>
    </div>
    <div id="overview-devices" style="margin-top:1rem;min-height:60px;max-height:120px;overflow:auto"></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>Core Features</h3>
  </div>
  <div class="grid">
    @foreach ($features as $feature)
    <div style="display:flex;align-items:center;gap:0.5rem">
      <span class="badge badge-green">✓</span>
      <span style="font-size:0.85rem">{{ $feature }}</span>
    </div>
    @endforeach
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>Device Status</h3>
  </div>
  @if (isset($devices) && count($devices) > 0)
    @foreach ($devices as $device)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid var(--border)">
      <div>
        <span style="font-family:monospace;font-size:0.85rem">{{ $device['id'] }}</span>
        <span style="color:var(--text3);font-size:0.75rem;margin-left:0.5rem">{{ $device['type'] }}</span>
      </div>
      <span class="badge {{ $device['state'] === 'device' ? 'badge-green' : '' }}">
        {{ $device['state'] }}
      </span>
    </div>
    @endforeach
  @else
    <div style="color:var(--text3);font-size:0.85rem;padding:1rem 0;text-align:center">
      No devices connected
    </div>
  @endif
</div>

<script>
function checkServer() {
  const el = document.getElementById('overview-devices');
  el.innerHTML = '<div class="spinner"></div> Checking...';
  fetch('/api/status')
    .then(r => r.json())
    .then(d => {
      if (d.status === 'ok') {
        el.innerHTML = '<span class="ok">✓ Connected: GSMSDK v' + d.version + '</span>';
      } else {
        el.innerHTML = '<span class="err">✗ ' + (d.error || 'Unknown error') + '</span>';
      }
    })
    .catch(e => el.innerHTML = '<span class="err">✗ ' + e.message + '</span>');
}

function listDevices() {
  const el = document.getElementById('overview-devices');
  el.innerHTML = '<div class="spinner"></div> Loading...';
  fetch('/api/devices')
    .then(r => r.json())
    .then(d => {
      if (d.devices && d.devices.length > 0) {
        let html = d.devices.map(dev => 
          '📱 ' + dev.id + ' <span class="badge ' + (dev.type === 'device' ? 'badge-green' : '') + '">' + dev.type + '</span><br/>'
        ).join('');
        el.innerHTML = html;
      } else {
        el.innerHTML = '<span class="err">No devices connected</span>';
      }
    })
    .catch(e => el.innerHTML = '<span class="err">✗ ' + e.message + '</span>');
}
</script>
@endsection
