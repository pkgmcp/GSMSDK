<?xml version="1.0" encoding="UTF-8"?>
<section class="page active" id="page-overview">
  <h2>🏠 Overview</h2>
  <p>GSMSDK - Full-Stack PHP Framework with ADB &amp; Fastboot Integration</p>

  <div class="grid-2" style="margin:1.5rem 0">
    <div class="card">
      <div class="card-header">
        <h3>Framework Information</h3>
      </div>
      <div class="stat-row"><span class="stat-label">Version</span><span class="stat-value"><?= $version ?></span></div>
      <div class="stat-row"><span class="stat-label">Environment</span><span class="stat-value"><?= ucfirst($environment) ?></span></div>
      <div class="stat-row"><span class="stat-label">PHP Version</span><span class="stat-value"><?= PHP_VERSION ?></span></div>
      <div class="stat-row"><span class="stat-label">License</span><span class="stat-value">MIT</span></div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>Quick Actions</h3>
      </div>
      <div class="field-row">
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
      <?php foreach ($features as $feature): ?>
      <div>
        <span class="badge badge-green">✓</span> <?= $this->escape($feature) ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3>Recent Activity</h3>
    </div>
    <div class="output" style="min-height:100px">
      <span class="info">No recent activity</span>
    </div>
  </div>
</section>

<script>
function checkServer() {
  const el = document.getElementById('overview-devices');
  el.innerHTML = '<div class="spinner"></div> Checking...';
  fetch('src/api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=server_status'
  })
  .then(r => r.json())
  .then(d => {
    if (d.status === 'connected') {
      el.innerHTML = `<span class="ok">✓ Connected: ADB Server v${d.version}</span>`;
    } else {
      el.innerHTML = `<span class="err">✗ ${d.message}</span>`;
    }
  })
  .catch(e => el.innerHTML = `<span class="err">✗ ${e.message}</span>`);
}

function listDevices() {
  const el = document.getElementById('overview-devices');
  el.innerHTML = '<div class="spinner"></div> Loading...';
  fetch('src/api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=list_devices'
  })
  .then(r => r.json())
  .then(d => {
    if (d.devices && d.devices.length > 0) {
      let html = d.devices.map(dev => 
        `📱 ${dev.id} <span class="badge ${dev.type==='device'?'badge-green':''}">${dev.type}</span><br/>`
      ).join('');
      el.innerHTML = html;
    } else {
      el.innerHTML = '<span class="err">No devices connected</span>';
    }
  })
  .catch(e => el.innerHTML = `<span class="err">✗ ${e.message}</span>`);
}
</script>
