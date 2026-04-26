<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/flash.xhtml">
  <ui:define name="content">
    <script type="text/javascript">
      document.title = 'File Manager | GSMSDK';
    </script>
    
    <div class="animate-in stagger-1">
      <h1 class="text-2xl mb-1" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-0.03em;">
        📂 File Manager
      </h1>
      <p style="color:var(--text2);font-size:0.9rem;">Browse and transfer files between device and computer</p>
    </div>
    
    <!-- Navigation -->
    <div class="card animate-in stagger-2">
      <div class="card-header">
        <h3 class="card-title">
          <span>📁</span> File System
        </h3>
        <div class="device-indicator">
          <span class="device-indicator-dot online"></span>
          <span>emulator-5554</span>
        </div>
      </div>
      
      <!-- Breadcrumb -->
      <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap">
        <span onclick="navigateToPath('/')" style="cursor:pointer;color:var(--accent2);font-size:0.85rem;">/</span>
        <span style="color:var(--text3)">›</span>
        <span onclick="navigateToPath('/sdcard')" style="cursor:pointer;color:var(--text2);font-size:0.85rem;">sdcard</span>
        <span style="color:var(--text3)">›</span>
        <span onclick="navigateToPath('/sdcard/Download')" style="cursor:pointer;color:var(--text2);font-size:0.85rem;">Download</span>
      </div>
      
      <!-- Path Bar -->
      <div class="field-row" style="margin-bottom:0.5rem">
        <div class="field">
          <input type="text" class="field-input mono" id="currentPath" value="/sdcard/Download"/>
        </div>
        <button class="btn btn-secondary" onclick="navigateToPath(document.getElementById('currentPath').value)">
          Go
        </button>
        <button class="btn btn-secondary" onclick="refreshFiles()">
          ↻ Refresh
        </button>
      </div>
      
      <!-- File Table -->
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th style="width:30px"></th>
              <th>Name</th>
              <th>Type</th>
              <th>Size</th>
              <th>Modified</th>
              <th>Permissions</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="fileTableBody">
            <tr>
              <td>📁</td>
              <td><a href="#" onclick="navigateToPath('/sdcard')" style="color:var(--accent2)">..</a></td>
              <td>Directory</td>
              <td>-</td>
              <td class="mono">Apr 26 08:00</td>
              <td class="mono">rwxr-xr-x</td>
              <td></td>
            </tr>
            <tr>
              <td>📁</td>
              <td><a href="#" onclick="navigateToPath('/sdcard/Download/Apps')" style="color:var(--accent2)">Apps</a></td>
              <td>Directory</td>
              <td>-</td>
              <td class="mono">Apr 25 15:30</td>
              <td class="mono">rwxr-xr-x</td>
              <td>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">Open</button>
              </td>
            </tr>
            <tr>
              <td>📄</td>
              <td>update.zip</td>
              <td class="mono">.zip</td>
              <td class="mono">124.5 MB</td>
              <td class="mono">Apr 24 22:15</td>
              <td class="mono">rw-r--r--</td>
              <td>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">Pull</button>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">Install</button>
              </td>
            </tr>
            <tr>
              <td>📄</td>
              <td>app-debug.apk</td>
              <td class="mono">.apk</td>
              <td class="mono">24.8 MB</td>
              <td class="mono">Apr 23 10:45</td>
              <td class="mono">rw-r--r--</td>
              <td>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">Pull</button>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">Install</button>
              </td>
            </tr>
            <tr>
              <td>📄</td>
              <td>photo_20240420.jpg</td>
              <td class="mono">.jpg</td>
              <td class="mono">3.2 MB</td>
              <td class="mono">Apr 20 14:22</td>
              <td class="mono">rw-r--r--</td>
              <td>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">Pull</button>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">View</button>
              </td>
            </tr>
            <tr>
              <td>📄</td>
              <td>logcat.txt</td>
              <td class="mono">.txt</td>
              <td class="mono">4.1 MB</td>
              <td class="mono">Apr 19 18:00</td>
              <td class="mono">rw-r--r--</td>
              <td>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">Pull</button>
                <button class="btn btn-secondary" style="font-size:0.65rem;padding:0.15rem 0.3rem">View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- File Operations -->
    <div class="card animate-in stagger-3">
      <div class="card-header">
        <h3 class="card-title">
          <span>⚡</span> Quick Operations
        </h3>
      </div>
      
      <div class="btn-group" style="flex-wrap:wrap">
        <button class="btn btn-primary">
          ⬆ Push to Device
        </button>
        <button class="btn btn-secondary">
          ⬇ Pull from Device
        </button>
        <button class="btn btn-secondary">
          📁 Create Directory
        </button>
        <button class="btn btn-secondary">
          🗑️ Delete
        </button>
        <button class="btn btn-secondary">
          🔄 Rename
        </button>
        <button class="btn btn-secondary">
          📋 Copy
        </button>
      </div>
    </div>
    
    <!-- Upload Zone -->
    <div class="card animate-in stagger-4">
      <div class="card-header">
        <h3 class="card-title">
          <span>📤</span> Upload to Device
        </h3>
      </div>
      
      <div style="border: 2px dashed var(--border); border-radius: 12px; padding: 2rem; text-align: center;">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📁</div>
        <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">Drop files here or click to browse</div>
        <div style="font-size: 0.75rem; color: var(--text3);">Supports .apk, .img, .zip, and all file types</div>
      </div>
      
      <div class="field-row">
        <div class="field">
          <input type="text" class="field-input mono" placeholder="/sdcard/Download/"/>
        </div>
        <button class="btn btn-primary">Upload</button>
      </div>
    </div>
    
    <!-- Sync Status -->
    <div class="card animate-in stagger-5">
      <div class="card-header">
        <h3 class="card-title">
          <span>📊</span> Sync Status
        </h3>
      </div>
      <div class="card-grid">
        <div class="stat-card">
          <div class="stat-value">12</div>
          <div class="stat-label">Files Uploaded</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">8</div>
          <div class="stat-label">Files Downloaded</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">4.2 GB</div>
          <div class="stat-label">Total Transferred</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">99.2%</div>
          <div class="stat-label">Success Rate</div>
        </div>
      </div>
    </div>
  </ui:define>
</ui:composition>
