<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo isset($title) ? htmlspecialchars($title) . ' | ' : '' ?>GSMSDK Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&amp;family=JetBrains+Mono:wght@400;500&amp;display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg: #0a0a0f;
      --bg2: #111118;
      --bg3: #18181f;
      --bg4: #20202a;
      --border: #2a2a38;
      --border2: #353545;
      --accent: #a855f7;
      --accent2: #c084fc;
      --accent3: #9333ea;
      --teal: #14b8a6;
      --cyan: #06b6d4;
      --red: #f43f5e;
      --green: #10b981;
      --yellow: #eab308;
      --text: #e8e8f0;
      --text2: #a0a0b0;
      --text3: #606070;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { background: var(--bg); color: var(--text); font-family: 'Space Grotesk', sans-serif; }
    code, .mono { font-family: 'JetBrains Mono', monospace; }
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: var(--bg); }
    ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--border); }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-16px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    
    .animate-in { animation: fadeIn 0.4s ease-out forwards; }
    .stagger-1 { animation-delay: 0.05s; }
    .stagger-2 { animation-delay: 0.1s; }
    .stagger-3 { animation-delay: 0.15s; }
    .stagger-4 { animation-delay: 0.2s; }
    
    .card { background: var(--bg2); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; backdrop-filter: blur(10px); }
    .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .card-title { font-size: 1rem; font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 0.5rem; }
    
    .sidebar { width: 240px; background: var(--bg2); border-right: 1px solid var(--border); position: fixed; top: 64px; left: 0; bottom: 0; overflow-y: auto; transition: transform 0.3s ease; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .nav-section { padding: 0.75rem 1rem 0.25rem; font-size: 0.65rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text3); }
    .nav-item { display: flex; align-items: center; gap: 0.625rem; padding: 0.6rem 1rem 0.6rem 1.25rem; font-size: 0.8rem; color: var(--text2); cursor: pointer; transition: all 0.15s ease; border-left: 2px solid transparent; text-decoration: none; }
    .nav-item:hover { color: var(--text); background: rgba(168, 85, 247, 0.05); border-left-color: var(--accent); }
    .nav-item.active { color: var(--accent2); border-left-color: var(--accent); background: rgba(168, 85, 247, 0.08); font-weight: 500; }
    .nav-icon { font-size: 1rem; width: 20px; text-align: center; }
    
    .header { position: fixed; top: 0; left: 0; right: 0; height: 64px; background: var(--bg2); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 1.5rem; z-index: 100; backdrop-filter: blur(10px); }
    .header-left { display: flex; align-items: center; gap: 1rem; }
    .menu-toggle { background: none; border: none; color: var(--text); cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: background 0.15s; }
    .menu-toggle:hover { background: var(--bg3); }
    .search-box { background: var(--bg3); border: 1px solid var(--border); border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.85rem; color: var(--text); width: 300px; transition: all 0.15s; }
    .search-box:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.15); }
    .header-right { margin-left: auto; display: flex; align-items: center; gap: 0.5rem; }
    .icon-btn { width: 40px; height: 40px; border-radius: 10px; background: transparent; border: none; color: var(--text2); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
    .icon-btn:hover { background: var(--bg3); color: var(--text); }
    .user-menu { display: flex; align-items: center; gap: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 10px; cursor: pointer; transition: background 0.15s; }
    .user-menu:hover { background: var(--bg3); }
    .avatar { width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, var(--accent), var(--teal)); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; }
    
    .stat-card { background: var(--bg3); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; text-align: center; transition: all 0.2s ease; }
    .stat-card:hover { border-color: var(--border2); transform: translateY(-2px); }
    .stat-value { font-size: 2rem; font-weight: 700; color: var(--accent2); font-family: 'JetBrains Mono', monospace; line-height: 1; }
    .stat-label { font-size: 0.75rem; color: var(--text3); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.5rem; }
    .stat-change { font-size: 0.75rem; margin-top: 0.25rem; }
    .stat-change.positive { color: var(--green); }
    .stat-change.negative { color: var(--red); }
    
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; font-size: 0.8rem; font-weight: 600; font-family: inherit; border-radius: 10px; border: none; cursor: pointer; transition: all 0.15s ease; }
    .btn-primary { background: linear-gradient(135deg, var(--accent) 0%, var(--accent3) 100%); color: #fff; box-shadow: 0 4px 20px rgba(168, 85, 247, 0.3); }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 30px rgba(168, 85, 247, 0.4); }
    .btn-secondary { background: var(--bg3); color: var(--text); border: 1px solid var(--border); }
    .btn-secondary:hover { border-color: var(--accent); color: var(--accent2); }
    .btn-danger { background: linear-gradient(135deg, var(--red) 0%, #dc2626 100%); color: #fff; }
    .btn-danger:hover { filter: brightness(1.1); }
    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
    
    .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; font-size: 0.7rem; font-weight: 600; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
    .badge-success { background: rgba(16, 185, 129, 0.15); color: var(--green); }
    .badge-danger { background: rgba(244, 63, 94, 0.15); color: var(--red); }
    .badge-warning { background: rgba(234, 179, 8, 0.15); color: var(--yellow); }
    .badge-info { background: rgba(6, 182, 212, 0.15); color: var(--cyan); }
    .badge-accent { background: rgba(168, 85, 247, 0.15); color: var(--accent2); }
    
    .table-wrap { overflow-x: auto; border-radius: 12px; }
    .table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .table th, .table td { padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid var(--border); }
    .table th { font-weight: 600; color: var(--text3); text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; background: var(--bg3); }
    .table tr:hover td { background: rgba(168, 85, 247, 0.03); }
    .table tr:last-child td { border-bottom: none; }
    
    .progress-wrap { width: 100%; background: var(--bg3); border-radius: 10px; overflow: hidden; }
    .progress-bar { height: 8px; border-radius: 10px; transition: width 0.3s ease; background: linear-gradient(90deg, var(--accent) 0%, var(--accent2) 100%); }
    
    .toast-container { position: fixed; top: 80px; right: 1.5rem; z-index: 1000; display: flex; flex-direction: column; gap: 0.5rem; }
    .toast { background: var(--bg2); border: 1px solid var(--border); border-radius: 12px; padding: 1rem 1.25rem; box-shadow: 0 10px 40px rgba(0,0,0,0.4); min-width: 300px; backdrop-filter: blur(10px); transform: translateX(120%); transition: transform 0.3s ease; }
    .toast.show { transform: translateX(0); }
    .toast.success { border-color: var(--green); }
    .toast.error { border-color: var(--red); }
    
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 200; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: all 0.2s ease; }
    .modal-overlay.show { opacity: 1; visibility: visible; }
    .modal { background: var(--bg2); border: 1px solid var(--border); border-radius: 20px; padding: 2rem; min-width: 400px; max-width: 600px; max-height: 80vh; overflow-y: auto; transform: scale(0.9); transition: transform 0.2s ease; }
    .modal-overlay.show .modal { transform: scale(1); }
    .modal-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text); }
    
    .grid-cols-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    @media (max-width: 1024px) { .grid-cols-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .grid-cols-stats { grid-template-columns: 1fr; } .sidebar { display: none; } }
    
    .chart-container { background: var(--bg3); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }
    .chart-bar { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .chart-bar-label { width: 120px; font-size: 0.85rem; color: var(--text2); }
    .chart-bar-value { width: 60px; font-size: 0.85rem; color: var(--accent2); font-family: 'JetBrains Mono', monospace; }
    .chart-bar-track { flex: 1; height: 8px; background: var(--bg4); border-radius: 4px; overflow: hidden; }
    .chart-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--accent), var(--accent2)); }
    
    .footer { border-top: 1px solid var(--border); padding: 1rem 1.5rem; background: var(--bg2); margin-top: auto; }
    .footer-text { font-size: 0.75rem; color: var(--text3); text-align: center; }
    
    .icon { width: 20px; height: 20px; }
    .icon-sm { width: 16px; height: 16px; }
    .icon-lg { width: 24px; height: 24px; }
  </style>
</head>
<body>
@yield('content')
</body>
</html>
