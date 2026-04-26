<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/flash.xhtml">
  <ui:define name="content">
    <script type="text/javascript">
      document.title = 'ADB Tools | GSMSDK';
    </script>
    
    <div class="animate-in stagger-1">
      <h1 class="text-2xl mb-1" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-0.03em;">
        🔌 ADB Tools & Commands
      </h1>
      <p style="color:var(--text2);font-size:0.9rem;">Interactive ADB shell and device management</p>
    </div>
    
    <!-- Interactive Shell -->
    <div class="card animate-in stagger-2">
      <div class="card-header">
        <h3 class="card-title">
          <span>💻</span> Interactive Shell
        </h3>
        <span class="badge-success" id="shellStatus">Ready</span>
      </div>
      
      <div class="field-row" style="margin-bottom:0.5rem">
        <div class="field" style="flex:1">
          <div class="field-row">
            <input type="text" class="field-input mono" id="shellInput" 
                   placeholder="Enter ADB shell command (e.g., ls /sdcard)" 
                   onkeydown="handleShellKeyDown(event)"/>
            <button class="btn btn-primary" onclick="executeShell()" style="white-space:nowrap">
              ▶ Run
            </button>
          </div>
        </div>
        <button class="btn btn-secondary" onclick="clearShell()">
          🧹 Clear
        </button>
      </div>
      
      <!-- Command History -->
      <div style="margin-bottom:0.5rem">
        <div style="display:flex;gap:0.25rem;flex-wrap:wrap">
          <span class="badge" style="cursor:pointer" onclick="loadHistoryCmd(this)">ls /system</span>
          <span class="badge" style="cursor:pointer" onclick="loadHistoryCmd(this)">pm list packages</span>
          <span class="badge" style="cursor:pointer" onclick="loadHistoryCmd(this)">ps -A</span>
          <span class="badge" style="cursor:pointer" onclick="loadHistoryCmd(this)">dumpsys battery</span>
          <span class="badge" style="cursor:pointer" onclick="loadHistoryCmd(this)">getprop</span>
        </div>
      </div>
      
      <!-- Shell Output -->
      <div class="output-wrap" style="max-height:400px">
        <div class="output-header">
          <span class="output-header-text">Shell Output</span>
          <span class="badge-info mono" id="shellPrompt">$</span>
        </div>
        <div class="output-body output-cmd" id="shellOutput">
<span style="color:var(--text3)">$ adb shell</span>
<span style="color:var(--text3)"># Type commands below or use Quick Commands</span>

        </div>
      </div>
    </div>
    
    <!-- File Transfer -->
    <div class="card animate-in stagger-3">
      <div class="card-header">
        <h3 class="card-title">
          <span>📂</span> File Transfer
        </h3>
      </div>
      
      <div class="field-row">
        <div class="field">
          <label class="field-label">Push to Device</label>
          <div class="field-row">
            <input type="text" class="field-input mono" id="pushLocal" placeholder="/local/path/file" style="flex:2"/>
            <input type="text" class="field-input mono" id="pushRemote" placeholder="/sdcard/path/" style="flex:1"/>
            <button class="btn btn-secondary" onclick="pushFile()">
              ⬆ Push
            </button>
          </div>
        </div>
      </div>
      
      <div class="field-row">
        <div class="field">
          <label class="field-label">Pull from Device</label>
          <div class="field-row">
            <input type="text" class="field-input mono" id="pullRemote" placeholder="/sdcard/path/file" style="flex:2"/>
            <input type="text" class="field-input mono" id="pullLocal" placeholder="/local/path/" style="flex:1"/>
            <button class="btn btn-secondary" onclick="pullFile()">
              ⬇ Pull
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- App Management -->
    <div class="card animate-in stagger-4">
      <div class="card-header">
        <h3 class="card-title">
          <span>📦</span> App Management
        </h3>
      </div>
      
      <div class="field-row">
        <div class="field">
          <label class="field-label">Install APK</label>
          <div class="field-row">
            <input type="text" class="field-input mono" id="apkPath" placeholder="/path/to/app.apk"/>
            <button class="btn btn-secondary" onclick="installApk()">
              📥 Install
            </button>
            <button class="btn btn-secondary" onclick="uninstallApk()">
              🗑️ Uninstall
            </button>
          </div>
        </div>
      </div>
      
      <div class="field-row">
        <div class="field">
          <label class="field-label">Package Name</label>
          <input type="text" class="field-input mono" id="packageName" placeholder="com.example.app"/>
        </div>
        <div class="field">
          <label class="field-label">Options</label>
          <div class="field-row">
            <label style="cursor:pointer;font-size:0.85rem;display:flex;align-items:center;gap:0.25rem">
              <input type="checkbox" id="rFlag" style="accent-color:var(--accent);"/>
              <span>-r (reinstall)</span>
            </label>
            <label style="cursor:pointer;font-size:0.85rem;display:flex;align-items:center;gap:0.25rem">
              <input type="checkbox" id="dFlag" style="accent-color:var(--accent);"/>
              <span>-d (downgrade)</span>
            </label>
            <label style="cursor:pointer;font-size:0.85rem;display:flex;align-items:center;gap:0.25rem">
              <input type="checkbox" id="gFlag" style="accent-color:var(--accent);"/>
              <span>-g (all perms)</span>
            </label>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Screen Capture -->
    <div class="card animate-in stagger-5">
      <div class="card-header">
        <h3 class="card-title">
          <span>🖥️</span> Screen Capture
        </h3>
      </div>
      
      <div class="field-row">
        <button class="btn btn-primary" onclick="captureScreen()">
          📸 Capture Screen
        </button>
        <button class="btn btn-secondary" onclick="startScreenRecord()">
          🎬 Record Screen
        </button>
        <button class="btn btn-secondary" onclick="stopScreenRecord()">
          ⏹ Stop Recording
        </button>
      </div>
      
      <div id="screenCaptureOutput" style="display:none;margin-top:1rem">
        <img id="capturedImage" style="max-width:100%;border-radius:8px;border:1px solid var(--border)"/>
      </div>
    </div>
    
    <!-- Logcat -->
    <div class="card animate-in stagger-6">
      <div class="card-header">
        <h3 class="card-title">
          <span>📜</span> Logcat Controls
        </h3>
      </div>
      
      <div class="field-row">
        <button class="btn btn-secondary" onclick="startLogcat()">
          ▶ Start Logcat
        </button>
        <button class="btn btn-secondary" onclick="stopLogcat()">
          ⏸ Stop Logcat
        </button>
        <button class="btn btn-secondary" onclick="clearLogcat()">
          🧹 Clear
        </button>
        <select class="field-select" id="logLevel" style="width:120px">
          <option value="V">Verbose (V)</option>
          <option value="D">Debug (D)</option>
          <option value="I" selected>Info (I)</option>
          <option value="W">Warning (W)</option>
          <option value="E">Error (E)</option>
          <option value="F">Fatal (F)</option>
        </select>
        <input type="text" class="field-input mono" id="logFilter" placeholder="Filter tag/package" style="width:160px"/>
      </div>
    </div>
  </ui:define>
</ui:composition>
