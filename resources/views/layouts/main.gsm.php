<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $title ?? 'GSMSDK' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg: #06060b;
      --bg2: #0c0c14;
      --bg3: #12121e;
      --bg4: #1a1a2a;
      --border: #1e1e30;
      --border2: #2a2a40;
      --accent: #6366f1;
      --accent2: #818cf8;
      --accent3: #4f46e5;
      --green: #22c55e;
      --red: #ef4444;
      --yellow: #f59e0b;
      --cyan: #06b6d4;
      --text: #e2e8f0;
      --text2: #94a3b8;
      --text3: #64748b;
    }
    * { box-sizing: border-box; margin: 0; padding: 0 }
    html { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif }
    body { min-height: 100vh }
    code, .mono { font-family: 'JetBrains Mono', monospace }
    ::-webkit-scrollbar { width: 5px }
    ::-webkit-scrollbar-track { background: var(--bg) }
    ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px }
    .layout { display: flex; min-height: 100vh }
    .sidebar { width: 240px; flex-shrink: 0; background: var(--bg2); border-right: 1px solid var(--border); position: sticky; top: 0; height: 100vh; overflow-y: auto }
    .main { flex: 1; min-width: 0; padding: 2rem 2.5rem 4rem }
    .nav-section { padding: .75rem 0 .25rem; font-size: .6rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: var(--text3); padding-left: 1.25rem }
    .nav-item { display: flex; align-items: center; gap: .5rem; padding: .38rem 1.25rem .38rem 1.5rem; font-size: .8rem; color: var(--text2); cursor: pointer; transition: .15s; border-left: 2px solid transparent; text-decoration: none }
    .nav-item:hover { color: var(--text); background: rgba(99,102,241,.05) }
    .nav-item.active { color: var(--accent2); border-left-color: var(--accent); background: rgba(99,102,241,.07); font-weight: 500 }
    .nav-icon { font-size: .9rem; width: 20px; text-align: center }
    h2 { font-size: 1.35rem; font-weight: 700; letter-spacing: -.03em; margin-bottom: .25rem }
    h3 { font-size: .95rem; font-weight: 600; margin: 1.25rem 0 .5rem; color: var(--accent2) }
    p { color: var(--text2); font-size: .85rem; line-height: 1.65; margin-bottom: .75rem }
    .card { background: var(--bg3); border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem }
    .card:hover { border-color: var(--border2) }
    .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem }
    .badge { display: inline-block; padding: .2rem .6rem; background: var(--border2); border-radius: 6px; font-size: .75rem; color: var(--text2) }
    .badge-green { background: rgba(34,197,94,.15); color: var(--green) }
    .badge-red { background: rgba(239,68,68,.15); color: var(--red) }
    .btn { display: inline-block; padding: .5rem 1rem; background: var(--accent); color: #fff; border: none; border-radius: 6px; font-size: .85rem; font-weight: 500; cursor: pointer; transition: background .2s; text-decoration: none }
    .btn:hover { background: var(--accent2) }
    .btn-secondary { background: transparent; border: 1px solid var(--border); color: var(--text) }
    .btn-secondary:hover { background: var(--bg2); border-color: var(--accent) }
    .btn-danger { background: var(--red) }
    .btn-danger:hover { background: #dc2626 }
    .output { background: var(--bg2); border: 1px solid var(--border); border-radius: 6px; padding: .75rem; margin-top: .5rem; min-height: 40px; font-family: 'JetBrains Mono', monospace; font-size: .8rem; white-space: pre-wrap; overflow: auto }
    .ok { color: var(--green) }
    .err { color: var(--red) }
    .info { color: var(--cyan) }
    .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid var(--border); border-top-color: var(--accent); border-radius: 50%; animation: spin .8s linear infinite }
    @keyframes spin { to { transform: rotate(360deg) } }
    .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem }
    @media (max-width: 768px) { .grid { grid-template-columns: 1fr } }
    .field { margin-top: .75rem }
    .field label { display: block; font-size: .75rem; color: var(--text3); margin-bottom: .25rem; text-transform: uppercase; letter-spacing: .05em }
    .field-row { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap }
    input[type="text"], input[type="number"], select, textarea { background: var(--bg); border: 1px solid var(--border); border-radius: 6px; padding: .5rem .75rem; color: var(--text); font-size: .85rem; font-family: inherit; width: 100%; transition: border-color .2s }
    input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent) }
    .mono { font-family: 'JetBrains Mono', monospace }
    .text-green-400 { color: var(--green) }
    .text-xs { font-size: .75rem }
    .text-sm { font-size: .85rem }
  </style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="logo">
      <h1>📱 GSMSDK</h1>
      <span>Full-Stack PHP Framework</span>
      <div style="margin-top:.5rem;font-size:.7rem;color:var(--text3)">
        @if (isset($version))
          v{{ $version }}
        @endif
      </div>
    </div>
    
    <div class="nav-section">Overview</div>
    <a href="/" class="nav-item active"><span class="nav-icon">🏠</span> Overview</a>
    <a href="/dashboard" class="nav-item"><span class="nav-icon">📊</span> Dashboard</a>
    
    <div class="nav-section">Device Control</div>
    <a href="/devices" class="nav-item"><span class="nav-icon">📱</span> Devices</a>
    <a href="/shell" class="nav-item"><span class="nav-icon">💻</span> Shell</a>
    <a href="/screen" class="nav-item"><span class="nav-icon">🖥️</span> Screen</a>
    
    <div class="nav-section">System</div>
    <a href="#" class="nav-item"><span class="nav-icon">⚡</span> Fastboot</a>
    <a href="#" class="nav-item"><span class="nav-icon">⚙️</span> System</a>
    
    <div class="nav-section">Advanced</div>
    <a href="/logcat" class="nav-item"><span class="nav-icon">📜</span> Logcat</a>
    <a href="/terminal" class="nav-item"><span class="nav-icon">💻</span> Terminal</a>
  </aside>
  
  <main class="main">
    @yield('content')
  </main>
</div>
</body>
</html>
