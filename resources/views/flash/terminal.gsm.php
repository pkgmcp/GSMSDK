<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/flash.xhtml">
  <ui:define name="content">
    <script type="text/javascript">
      document.title = 'Terminal | GSMSDK';
    </script>
    
    <div class="animate-in stagger-1">
      <h1 class="text-2xl mb-1" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-0.03em;">
        💻 Integrated Terminal
      </h1>
      <p style="color:var(--text2);font-size:0.9rem;">Direct ADB/ADB shell access via PHP backend</p>
    </div>
    
    <div class="card animate-in stagger-2">
      <div class="card-header">
        <h3 class="card-title">
          <span>⚙️</span> Terminal - {{ $device ?? 'emulator-5554' }}
        </h3>
        <div class="btn-group">
          <button class="btn btn-secondary" onclick="terminalResize()">
            🔍 Resize
          </button>
          <button class="btn btn-secondary" onclick="terminalClear()">
            🧹 Clear
          </button>
          <button class="btn btn-secondary" onclick="terminalCopy()">
            📋 Copy All
          </button>
        </div>
      </div>
      
      <div class="output-wrap" style="background:var(--bg);border-color:var(--bg4);min-height:400px">
        <div class="output-body mono" id="terminalOutput" style="color:var(--text);line-height:1.6">
<span style="color:var(--cyan)">GSMSDK Terminal v2.0.0</span>
<span style="color:var(--text3)">Type ADB/ADB shell commands below</span>

<span style="color:var(--text3)">$ </span><span style="color:var(--green)">echo "Ready for commands..."</span>

        </div>
      </div>
      
      <div class="field-row" style="margin-top:0.5rem">
        <div class="field" style="flex:1">
          <div class="field-row">
            <input type="text" class="field-input mono" id="terminalInput" 
                   placeholder="Enter command (e.g., shell ls /sdcard)" 
                   style="border:none;background:var(--bg2)"
                   onkeydown="handleTerminalKey(event)"/>
            <button class="btn btn-primary" onclick="terminalSend()" style="border-radius:0 10px 10px 0">
              Send
            </button>
          </div>
        </div>
      </div>
      
      <div style="margin-top:0.5rem;display:flex;gap:0.5rem;flex-wrap:wrap">
        <span class="badge badge-info" style="cursor:pointer" onclick="quickCmd('shell ls /sdcard')">ls /sdcard</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="quickCmd('shell pm list packages')">list pkgs</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="quickCmd('shell dumpsys cpuinfo')">cpu info</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="quickCmd('shell dumpsys meminfo')">mem info</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="quickCmd('shell getprop ro.build.version.release')">Android ver</span>
        <span class="badge badge-info" style="cursor:pointer" onclick="quickCmd('reboot')">reboot</span>
      </div>
    </div>
    
    <!-- Device Commands Section -->
    <div class="card animate-in stagger-3">
      <div class="card-header">
        <h3 class="card-title">
          <span>📱</span> Device Control
        </h3>
      </div>
      
      <div class="card-grid">
        <div class="stat-card">
          <div class="stat-value">34</div>
          <div class="stat-label">Android API</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">x86_64</div>
          <div class="stat-label">ARCH</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">Google</div>
          <div class="stat-label">API Level</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">14</div>
          <div class="stat-label">Version</div>
        </div>
      </div>
      
      <div class="btn-group" style="margin-top:1rem">
        <button class="btn btn-secondary" onclick="terminalSendCmd('install ')" style="display:none">📥 Install APK</button>
        <button class="btn btn-secondary" onclick="terminalSendCmd('uninstall ')" style="display:none">🗑️ Uninstall</button>
        <button class="btn btn-secondary" onclick="terminalSendCmd('shell screencap ')" style="display:none">📸 Screenshot</button>
        <button class="btn btn-secondary" onclick="terminalSendCmd('shell am start -n ')" style="display:none">🚀 Launch</button>
        <button class="btn btn-danger" onclick="terminalSendCmd('reboot ')" style="display:none">🔄 Reboot</button>
      </div>
    </div>
    
    <!-- Command Reference -->
    <div class="card animate-in stagger-4">
      <div class="card-header">
        <h3 class="card-title">
          <span>📖</span> Command Reference
        </h3>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Command</th>
              <th>Action</th>
              <th>Example</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="mono">shell &lt;cmd&gt;</td>
              <td>Run shell command</td>
              <td><span class="mono">shell ls /system</span></td>
            </tr>
            <tr>
              <td class="mono">install &lt;apk&gt;</td>
              <td>Install APK</td>
              <td><span class="mono">install /sdcard/app.apk</span></td>
            </tr>
            <tr>
              <td class="mono">uninstall &lt;pkg&gt;</td>
              <td>Uninstall app</td>
              <td><span class="mono">uninstall com.example</span></td>
            </tr>
            <tr>
              <td class="mono">push &lt;l&gt; &lt;r&gt;</td>
              <td>Push file</td>
              <td><span class="mono">push file.txt /sdcard/</span></td>
            </tr>
            <tr>
              <td class="mono">pull &lt;r&gt; &lt;l&gt;</td>
              <td>Pull file</td>
              <td><span class="mono">pull /sdcard/file.txt .</span></td>
            </tr>
            <tr>
              <td class="mono">reboot</td>
              <td>Reboot device</td>
              <td><span class="mono">reboot bootloader</span></td>
            </tr>
            <tr>
              <td class="mono">logcat</td>
              <td>Show logs</td>
              <td><span class="mono">logcat -d</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </ui:define>
</ui:composition>
