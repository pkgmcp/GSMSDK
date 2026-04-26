<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($title ?? 'GSMSDK Flash Tool', ENT_XML1, 'UTF-8'); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&amp;family=Space+Grotesk:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
  <style type="text/css">
    :root {
      --bg: #050508;
      --bg2: #0c0c12;
      --bg3: #14141f;
      --bg4: #1c1c2a;
      --border: #242438;
      --border2: #2f2f4a;
      --accent: #8b5cf6;
      --accent2: #a78bfa;
      --accent3: #7c3aed;
      --teal: #14b8a6;
      --cyan: #06b6d4;
      --red: #f43f5e;
      --red2: #dc2626;
      --green: #10b981;
      --yellow: #eab308;
      --text: #e8e8f0;
      --text2: #9898a8;
      --text3: #5c5c70;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { background: var(--bg); color: var(--text); font-family: 'Space Grotesk', sans-serif; scroll-behavior: smooth; }
    body { min-height: 100vh; overflow-x: hidden; }
    code, .mono { font-family: 'JetBrains Mono', monospace; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: var(--bg); }
    ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--border); }
    
    /* Animations */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-16px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 1; } 100% { transform: scale(1.4); opacity: 0; } }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes breathe { 0%, 100% { opacity: 0.6; } 50% { opacity: 1; } }
    
    .animate-in { animation: fadeIn 0.4s ease-out forwards; }
    .animate-slide { animation: slideIn 0.3s ease-out forwards; }
    .stagger-1 { animation-delay: 0.05s; }
    .stagger-2 { animation-delay: 0.1s; }
    .stagger-3 { animation-delay: 0.15s; }
    .stagger-4 { animation-delay: 0.2s; }
    
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
    }
    
    /* Layout */
    .app-layout { display: flex; min-height: 100vh; }
    .sidebar {
      width: 260px; flex-shrink: 0; background: linear-gradient(180deg, var(--bg2) 0%, var(--bg) 100%);
      border-right: 1px solid var(--border); position: sticky; top: 0; height: 100vh; overflow-y: auto;
    }
    .main-content { flex: 1; min-width: 0; padding: 1.5rem 2rem 3rem; background: var(--bg); }
    
    /* Logo */
    .logo-section { padding: 1.25rem 1.25rem 1.5rem; border-bottom: 1px solid var(--border); }
    .logo { display: flex; align-items: center; gap: 0.75rem; }
    .logo-icon {
      width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--accent) 0%, var(--teal) 100%);
      display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
    }
    .logo h1 { font-size: 1.1rem; font-weight: 700; letter-spacing: -0.02em; }
    .logo span { font-size: 0.7rem; color: var(--text3); font-weight: 500; text-transform: uppercase; letter-spacing: 0.1em; }
    
    /* Navigation */
    .nav-section { padding: 0.75rem 1rem 0.25rem; font-size: 0.65rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text3); }
    .nav-item {
      display: flex; align-items: center; gap: 0.625rem; padding: 0.6rem 1rem 0.6rem 1.25rem; font-size: 0.8rem;
      color: var(--text2); cursor: pointer; transition: all 0.15s ease; border-left: 2px solid transparent;
      text-decoration: none; position: relative;
    }
    .nav-item:hover { color: var(--text); background: rgba(139, 92, 246, 0.05); border-left-color: var(--accent); }
    .nav-item.active { color: var(--accent2); border-left-color: var(--accent); background: rgba(139, 92, 246, 0.08); font-weight: 500; }
    .nav-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: var(--accent); border-radius: 0 2px 2px 0; }
    .nav-icon { font-size: 1rem; width: 20px; text-align: center; }
    
    /* Status bar */
    .status-bar {
      margin-top: auto; padding: 1rem; border-top: 1px solid var(--border);
      background: linear-gradient(0deg, var(--bg2) 0%, transparent 100%);
    }
    .status-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: var(--text3); margin-bottom: 0.5rem; }
    .status-dot {
      width: 8px; height: 8px; border-radius: 50%; position: relative;
      animation: breathe 2s ease-in-out infinite;
    }
    .status-dot.connected { background: var(--green); box-shadow: 0 0 12px var(--green); }
    .status-dot.disconnected { background: var(--red); box-shadow: 0 0 12px var(--red); }
    .status-dot.busy { background: var(--yellow); box-shadow: 0 0 12px var(--yellow); }
    
    /* Cards */
    .card {
      background: var(--bg2); border: 1px solid var(--border); border-radius: 14px; padding: 1.25rem;
      margin-bottom: 1rem; backdrop-filter: blur(10px); position: relative; overflow: hidden;
    }
    .card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent, var(--accent), transparent);
      opacity: 0.3;
    }
    .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .card-title { font-size: 0.85rem; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 0.5rem; }
    
    /* Grids */
    .card-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .stat-card {
      background: var(--bg3); border: 1px solid var(--border); border-radius: 10px; padding: 1rem;
      text-align: center; transition: all 0.2s ease;
    }
    .stat-card:hover { border-color: var(--border2); transform: translateY(-2px); }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--accent2); font-family: 'JetBrains Mono', monospace; }
    .stat-label { font-size: 0.7rem; color: var(--text3); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.25rem; }
    
    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
      padding: 0.625rem 1.25rem; font-size: 0.8rem; font-weight: 600; font-family: inherit;
      border-radius: 10px; border: none; cursor: pointer; transition: all 0.15s ease;
    }
    .btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
    .btn-primary {
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent3) 100%);
      color: #fff; box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 30px rgba(139, 92, 246, 0.4); }
    .btn-primary:active { transform: translateY(0); }
    .btn-secondary {
      background: var(--bg3); color: var(--text); border: 1px solid var(--border);
    }
    .btn-secondary:hover { border-color: var(--accent); color: var(--accent2); }
    .btn-danger {
      background: linear-gradient(135deg, var(--red) 0%, var(--red2) 100%);
      color: #fff;
    }
    .btn-danger:hover { filter: brightness(1.1); }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }
    .btn-group { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    
    /* Inputs */
    .field { margin-bottom: 1rem; }
    .field-label {
      display: block; font-size: 0.75rem; font-weight: 600; color: var(--text2);
      margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .field-input,
    .field-select,
    .field-textarea {
      width: 100%; padding: 0.625rem 0.875rem; font-size: 0.85rem; font-family: inherit;
      background: var(--bg3); border: 1px solid var(--border); border-radius: 10px; color: var(--text);
      transition: all 0.15s ease;
    }
    .field-input:focus,
    .field-select:focus,
    .field-textarea:focus {
      outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
    }
    .field-input::placeholder,
    .field-textarea::placeholder { color: var(--text3); }
    .field-row { display: flex; gap: 0.75rem; align-items: flex-start; }
    .field-row > * { flex: 1; }
    
    /* Tabs */
    .tabs { display: flex; gap: 0.25rem; background: var(--bg3); border-radius: 10px; padding: 0.25rem; margin-bottom: 1rem; }
    .tab {
      flex: 1; padding: 0.625rem 1rem; font-size: 0.75rem; font-weight: 600; font-family: inherit;
      border: none; background: transparent; color: var(--text2); border-radius: 8px; cursor: pointer;
      transition: all 0.15s ease; text-align: center;
    }
    .tab:hover { color: var(--text); }
    .tab.active { background: var(--accent); color: #fff; }
    
    /* Tables */
    .table-wrap { overflow-x: auto; border-radius: 10px; }
    .table {
      width: 100%; border-collapse: collapse; font-size: 0.85rem;
    }
    .table th, .table td {
      padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border);
    }
    .table th {
      font-weight: 600; color: var(--text3); text-transform: uppercase; font-size: 0.7rem;
      letter-spacing: 0.05em; background: var(--bg3);
    }
    .table tr:hover td { background: rgba(139, 92, 246, 0.03); }
    .table tr:last-child td { border-bottom: none; }
    
    /* Badges */
    .badge {
      display: inline-flex; align-items: center; padding: 0.2rem 0.6rem; font-size: 0.7rem;
      font-weight: 600; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .badge-success { background: rgba(16, 185, 129, 0.15); color: var(--green); }
    .badge-danger { background: rgba(244, 63, 94, 0.15); color: var(--red); }
    .badge-warning { background: rgba(234, 179, 8, 0.15); color: var(--yellow); }
    .badge-info { background: rgba(6, 182, 212, 0.15); color: var(--cyan); }
    .badge-accent { background: rgba(139, 92, 246, 0.15); color: var(--accent2); }
    
    /* Spinner */
    .spinner {
      display: inline-block; width: 18px; height: 18px; border: 2px solid var(--border);
      border-top-color: var(--accent); border-radius: 50%; animation: spin 0.8s linear infinite;
    }
    .spinner.sm { width: 14px; height: 14px; }
    
    /* Progress bar */
    .progress-wrap { width: 100%; background: var(--bg3); border-radius: 10px; overflow: hidden; }
    .progress-bar {
      height: 8px; border-radius: 10px; transition: width 0.3s ease;
      background: linear-gradient(90deg, var(--accent) 0%, var(--accent2) 100%);
    }
    
    /* Toast notifications */
    .toast-container {
      position: fixed; top: 1.5rem; right: 1.5rem; z-index: 1000; display: flex; flex-direction: column; gap: 0.5rem;
    }
    .toast {
      background: var(--bg2); border: 1px solid var(--border); border-radius: 12px; padding: 1rem 1.25rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4); min-width: 300px; backdrop-filter: blur(10px);
      transform: translateX(120%); transition: transform 0.3s ease;
    }
    .toast.show { transform: translateX(0); }
    .toast.success { border-color: var(--green); }
    .toast.error { border-color: var(--red); }
    .toast.warning { border-color: var(--yellow); }
    
    /* Output area */
    .output-wrap {
      background: var(--bg3); border: 1px solid var(--border); border-radius: 12px; overflow: hidden;
    }
    .output-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 0.625rem 1rem; background: var(--bg4); border-bottom: 1px solid var(--border);
    }
    .output-header-text { font-size: 0.7rem; color: var(--text3); text-transform: uppercase; letter-spacing: 0.1em; }
    .output-body {
      padding: 1rem; max-height: 400px; overflow-y: auto; font-family: 'JetBrains Mono', monospace;
      font-size: 0.8rem; line-height: 1.7; white-space: pre-wrap;
    }
    .output-cmd { color: var(--green); }
    .output-ok { color: var(--green); }
    .output-err { color: var(--red); }
    .output-info { color: var(--cyan); }
    .output-warn { color: var(--yellow); }
    
    /* Modal */
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); z-index: 200;
      display: flex; align-items: center; justify-content: center;
      opacity: 0; visibility: hidden; transition: all 0.2s ease;
    }
    .modal-overlay.show { opacity: 1; visibility: visible; }
    .modal {
      background: var(--bg2); border: 1px solid var(--border); border-radius: 16px;
      padding: 1.5rem; min-width: 400px; max-width: 600px; max-height: 80vh;
      overflow-y: auto; transform: scale(0.9); transition: transform 0.2s ease;
    }
    .modal-overlay.show .modal { transform: scale(1); }
    .modal-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text); }
    
    /* Device indicator */
    .device-indicator {
      display: inline-flex; align-items: center; gap: 0.5rem;
      padding: 0.375rem 0.75rem; background: var(--bg3); border-radius: 20px;
      font-size: 0.75rem;
    }
    .device-indicator-dot {
      width: 8px; height: 8px; border-radius: 50%; animation: breathe 1.5s ease-in-out infinite;
    }
    .device-indicator-dot.online { background: var(--green); }
    .device-indicator-dot.offline { background: var(--red); }
    
    /* Section title */
    .section-title {
      display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;
    }
    .section-title-line { flex: 1; height: 1px; background: var(--border); }
    
    /* Flash steps */
    .flash-step {
      display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; background: var(--bg3);
      border: 1px solid var(--border); border-radius: 12px; margin-bottom: 1rem;
    }
    .flash-step-num {
      width: 32px; height: 32px; border-radius: 50%; background: var(--bg);
      border: 2px solid var(--border); display: flex; align-items: center;
      justify-content: center; font-size: 0.8rem; font-weight: 700; color: var(--text3);
      flex-shrink: 0; transition: all 0.2s ease;
    }
    .flash-step.active .flash-step-num {
      background: var(--accent); border-color: var(--accent); color: #fff;
    }
    .flash-step-content h4 { font-size: 0.85rem; margin-bottom: 0.25rem; }
    .flash-step-content p { font-size: 0.8rem; color: var(--text2); }
    
    /* Quick stats banner */
    .banner {
      background: linear-gradient(135deg, var(--bg3) 0%, var(--bg4) 100%);
      border: 1px solid var(--border); border-radius: 14px; padding: 1rem;
      margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;
    }
    .banner-icon {
      width: 48px; height: 48px; border-radius: 12px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--teal) 100%);
      display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
    }
    .banner-content h3 { font-size: 0.95rem; margin-bottom: 0.25rem; }
    .banner-content p { font-size: 0.8rem; color: var(--text2); }
    
    /* Command history */
    .history-item {
      padding: 0.5rem; border-radius: 6px; font-size: 0.8rem; cursor: pointer;
      transition: background 0.15s ease; display: flex; align-items: center; gap: 0.5rem;
    }
    .history-item:hover { background: var(--bg4); }
    
    /* Responsive */
    @media (max-width: 1024px) {
      .sidebar { position: fixed; left: -260px; top: 0; height: 100vh; z-index: 50;
        transition: left 0.3s ease; }
      .sidebar.open { left: 0; }
      .main-content { padding: 1rem; }
    }
    @media (max-width: 640px) {
      .card-grid { grid-template-columns: 1fr; }
      .field-row { flex-direction: column; }
    }
  </style>
</head>
<body>
<div class="app-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo-section">
      <div class="logo">
        <div class="logo-icon">⚡</div>
        <div>
          <h1>GSMSDK Flash</h1>
          <span>Flash Tool v2.0</span>
        </div>
      </div>
    </div>
    
    <div class="nav-section">Main</div>
    <a class="nav-item active" onclick="navigateTo('dashboard')">
      <span class="nav-icon">📊</span>Dashboard
    </a>
    <a class="nav-item" onclick="navigateTo('devices')">
      <span class="nav-icon">📱</span>Devices
    </a>
    <a class="nav-item" onclick="navigateTo('flash')">
      <span class="nav-icon">⚡</span>Fastboot Flash
    </a>
    <a class="nav-item" onclick="navigateTo('adb')">
      <span class="nav-icon">🔌</span>ADB Tools
    </a>
    
    <div class="nav-section">System</div>
    <a class="nav-item" onclick="navigateTo('terminal')">
      <span class="nav-icon">💻</span>Terminal
    </a>
    <a class="nav-item" onclick="navigateTo('logs')">
      <span class="nav-icon">📜</span>Logcat
    </a>
    <a class="nav-item" onclick="navigateTo('files')">
      <span class="nav-icon">📂</span>Files
    </a>
    
    <div class="status-bar">
      <div class="status-item">
        <div class="status-dot connected" id="statusDot"></div>
        <span id="statusText">Ready</span>
      </div>
      <div class="status-item">
        <span>Device: <span class="mono" id="currentDevice">none</span></span>
      </div>
    </div>
  </aside>
  
  <!-- Main Content -->
  <main class="main-content" id="mainContent">
    <?php echo $content; ?>
  </main>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script type="text/javascript">
// Navigation
function navigateTo(page) {
  document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
  event.currentTarget.classList.add('active');
  
  const mainContent = document.getElementById('mainContent');
  mainContent.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:300px;"><div class="spinner"></div></div>';
  
  fetch('/flash/' + page)
    .then(r => r.text())
    .then(html => { mainContent.innerHTML = html; })
    .catch(err => { showToast('Failed to load page', 'error'); });
}

// Toast notifications
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = 'toast ' + type;
  toast.innerHTML = message;
  container.appendChild(toast);
  
  setTimeout(() => toast.classList.add('show'), 10);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// Device status
function updateDeviceStatus(deviceId, status) {
  const dot = document.getElementById('statusDot');
  const text = document.getElementById('statusText');
  const currentDev = document.getElementById('currentDevice');
  
  dot.className = 'status-dot ' + status;
  text.textContent = status.charAt(0).toUpperCase() + status.slice(1);
  if (deviceId) currentDev.textContent = deviceId;
}

// Initialize
updateDeviceStatus('emulator-5554', 'connected');
</script>
</body>
</html>
