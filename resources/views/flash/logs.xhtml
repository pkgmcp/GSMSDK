<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/flash.xhtml">
  <ui:define name="content">
    <script type="text/javascript">
      document.title = 'Logcat | GSMSDK';
    </script>
    
    <div class="animate-in stagger-1">
      <h1 class="text-2xl mb-1" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-0.03em;">
        📜 Logcat Monitor
      </h1>
      <p style="color:var(--text2);font-size:0.9rem;">Real-time Android logcat streaming</p>
    </div>
    
    <!-- Controls -->
    <div class="card animate-in stagger-2">
      <div class="card-header">
        <h3 class="card-title">
          <span>⚙️</span> Logcat Controls
        </h3>
        <div class="btn-group">
          <button class="btn btn-primary" id="btnStartLogcat" onclick="startLogcatStream()">
            ▶ Start Streaming
          </button>
          <button class="btn btn-secondary" id="btnStopLogcat" onclick="stopLogcatStream()" disabled>
            ⏸ Pause
          </button>
          <button class="btn btn-secondary" onclick="clearLogcatView()">
            🧹 Clear
          </button>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="field-row" style="margin-bottom:0.5rem">
        <div class="field">
          <label class="field-label">Log Level</label>
          <select class="field-select" id="logLevel" onchange="updateLogLevel()">
            <option value="V">Verbose</option>
            <option value="D">Debug</option>
            <option value="I" selected>Info</option>
            <option value="W">Warning</option>
            <option value="E">Error</option>
            <option value="F">Fatal</option>
            <option value="S">Silent</option>
          </select>
        </div>
        <div class="field">
          <label class="field-label">Filter Tag</label>
          <input type="text" class="field-input mono" id="filterTag" placeholder="ActivityManager|PackageManager*"/>
        </div>
        <div class="field">
          <label class="field-label">PID</label>
          <input type="text" class="field-input mono" id="filterPid" placeholder="12345" style="width:100px"/>
        </div>
        <div class="field">
          <label class="field-label">Options</label>
          <div class="field-row">
            <label style="cursor:pointer;display:flex;align-items:center;gap:0.25rem;font-size:0.85rem">
              <input type="checkbox" id="wrapLines" onchange="updatePreferences()"/>
              <span>Wrap lines</span>
            </label>
            <label style="cursor:pointer;display:flex;align-items:center;gap:0.25rem;font-size:0.85rem">
              <input type="checkbox" id="autoScroll" checked/>
              <span>Auto-scroll</span>
            </label>
            <label style="cursor:pointer;display:flex;align-items:center;gap:0.25rem;font-size:0.85rem">
              <input type="checkbox" id="showTimestamp" checked/>
              <span>Timestamp</span>
            </label>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Quick Tags -->
    <div class="card animate-in stagger-3" style="margin-bottom:0.5rem">
      <div class="card-header">
        <h3 class="card-title">
          <span>🏷️</span> Common Tags
        </h3>
      </div>
      <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('ActivityManager')">ActivityManager</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('PackageManager')">PackageManager</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('WindowManager')">WindowManager</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('PowerManagerService')">PowerManager</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('Bluetooth')">Bluetooth</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('Wifi')">Wifi</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('AudioFlinger')">Audio</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="applyTagFilter('SurfaceFlinger')">SurfaceFlinger</span>
      </div>
    </div>
    
    <!-- Log Output -->
    <div class="card animate-in stagger-4">
      <div class="card-header">
        <h3 class="card-title">
          <span>📤</span> Log Output
        </h3>
        <div class="btn-group">
          <button class="btn btn-secondary" onclick="saveLogToFile()">
            💾 Save Log
          </button>
          <button class="btn btn-secondary" onclick="copyLogToClipboard()">
            📋 Copy
          </button>
          <span class="badge badge-accent" id="logCount">0 lines</span>
        </div>
      </div>
      
      <div class="output-wrap" style="max-height:60vh;min-height:400px">
        <div class="output-header">
          <span class="output-header-text">Logcat Stream</span>
          <span class="badge-info" id="logStatus">Idle</span>
        </div>
        <div class="output-body output-cmd" id="logcatOutput" style="font-size:0.75rem;padding:0.5rem;">
<span style="color:var(--text3)">Logcat viewer ready.</span>
<span style="color:var(--text3)">Click Start Streaming to begin capturing logs.</span>

        </div>
      </div>
    </div>
    
    <!-- Saved Logs -->
    <div class="card animate-in stagger-5">
      <div class="card-header">
        <h3 class="card-title">
          <span>📁</span> Saved Log Sessions
        </h3>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Time</th>
              <th>Session</th>
              <th>Filter</th>
              <th>Lines</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="mono">--:--:--</td>
              <td>session_0.log</td>
              <td>-</td>
              <td class="mono">0</td>
              <td>
                <button class="btn btn-secondary" style="font-size:0.7rem;padding:0.2rem 0.4rem">View</button>
                <button class="btn btn-secondary" style="font-size:0.7rem;padding:0.2rem 0.4rem">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </ui:define>
</ui:composition>
