<aside class="sidebar" id="adminSidebar">
  <div class="logo-section" style="padding:1rem 1.25rem;border-bottom:1px solid var(--border)">
    <div class="logo" style="display:flex;align-items:center;gap:0.75rem">
      <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--teal));display:flex;align-items:center;justify-content:center;font-size:1.2rem">⚡</div>
      <div>
        <div style="font-size:0.85rem;font-weight:700;color:var(--text)">GSMSDK</div>
        <div style="font-size:0.65rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.05em">Admin Panel</div>
      </div>
    </div>
  </div>

  <nav style="padding:1rem 0">
    <div class="nav-section">OVERVIEW</div>
    <a class="nav-item active" href="/admin/dashboard">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
      </svg>
      Dashboard
    </a>
    <a class="nav-item" href="/admin/analytics">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 20V10M12 20V4M6 20v-6"/>
      </svg>
      Analytics
    </a>
    <a class="nav-item" href="/admin/devices">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
      </svg>
      Devices
    </a>

    <div class="nav-section">FLASH & ADB</div>
    <a class="nav-item" href="/admin/flash">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
      </svg>
      Fastboot Flash
    </a>
    <a class="nav-item" href="/admin/adb">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
      </svg>
      ADB Tools
    </a>
    <a class="nav-item" href="/admin/logs">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
      </svg>
      Logcat Monitor
    </a>
    <a class="nav-item" href="/admin/files">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
      </svg>
      File Manager
    </a>

    <div class="nav-section">SYSTEM</div>
    <a class="nav-item" href="/admin/terminal">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
        <path d="M12 12l4-4M8 12l-4-4M12 12v8"/>
      </svg>
      Terminal
    </a>
    <a class="nav-item" href="/admin/users">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      Users
    </a>
    <a class="nav-item" href="/admin/settings">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
      </svg>
      Settings
    </a>

    <div class="nav-section">API & TOOLS</div>
    <a class="nav-item" href="/api/docs">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/>
      </svg>
      API Explorer
    </a>
    <a class="nav-item" href="/api/status">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
      </svg>
      API Status
    </a>
  </nav>

  <div class="status-bar" style="position:absolute;bottom:0;left:0;right:0;padding:1rem;border-top:1px solid var(--border);background:var(--bg2)">
    <div class="status-item" style="margin-bottom:0.5rem">
      <span class="status-dot connected"></span>
      <span style="font-size:0.75rem">System Online</span>
    </div>
    <div class="status-item">
      <span style="font-size:0.75rem">v2.0.0</span>
    </div>
  </div>
</aside>
