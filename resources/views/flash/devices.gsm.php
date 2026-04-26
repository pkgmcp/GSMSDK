<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/flash.xhtml">
  <ui:define name="content">
    <script type="text/javascript">
      document.title = 'Devices | GSMSDK';
    </script>
    
    <div class="animate-in stagger-1">
      <h1 class="text-2xl mb-1" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-0.03em;">
        📱 Device Manager
      </h1>
      <p style="color:var(--text2);font-size:0.9rem;">Manage connected Android devices</p>
    </div>
    
    <!-- Quick Connect -->
    <div class="card animate-in stagger-2">
      <div class="card-header">
        <h3 class="card-title">
          <span>🔌</span> Quick Connect
        </h3>
      </div>
      
      <div class="field-row">
        <div class="field">
          <label class="field-label">Device Address</label>
          <div class="field-row">
            <input type="text" class="field-input mono" placeholder="192.168.1.100:5555" id="deviceAddress"/>
            <select class="field-select" id="connectMode" style="width:140px">
              <option value="tcp">TCP/IP</option>
              <option value="usb">USB</option>
            </select>
            <button class="btn btn-primary" onclick="connectDevice()">
              🔗 Connect
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Connected Devices -->
    <div class="card animate-in stagger-3">
      <div class="card-header">
        <h3 class="card-title">
          <span>📋</span> Connected Devices
        </h3>
        <div class="btn-group">
          <button class="btn btn-secondary" onclick="refreshDevices()">
            ↻ Refresh
          </button>
          <button class="btn btn-secondary" onclick="scanDevices()">
            🔍 Scan
          </button>
        </div>
      </div>
      
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Serial</th>
              <th>Model</th>
              <th>State</th>
              <th>Mode</th>
              <th>Product</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="deviceTableBody">
            <tr>
              <td colspan="6" style="text-align:center;padding:2rem;color:var(--text3);">
                <div class="spinner" style="margin:0 auto 0.5rem auto"></div>
                Loading devices...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Device Details -->
    <div class="card animate-in stagger-4" id="deviceDetailCard" style="display:none">
      <div class="card-header">
        <h3 class="card-title">
          <span>🔍</span> Device Details
        </h3>
        <span class="badge-accent" id="selectedDevice">emulator-5554</span>
      </div>
      
      <div class="card-grid" id="deviceProperties">
        <!-- Populated by JS -->
      </div>
    </div>
    
    <!-- ADB Operations -->
    <div class="card animate-in stagger-5">
      <div class="card-header">
        <h3 class="card-title">
          <span>⚙️</span> ADB Operations
        </h3>
      </div>
      
      <div class="field-row" style="flex-wrap:wrap">
        <div class="field">
          <button class="btn btn-secondary" onclick="runShell('shell getprop ro.build.version.release')">
            🔍 Get Version
          </button>
        </div>
        <div class="field">
          <button class="btn btn-secondary" onclick="runShell('shell screencap')">
            🖥️ Screenshot
          </button>
        </div>
        <div class="field">
          <button class="btn btn-secondary" onclick="runShell('shell dumpsys battery')">
            🔋 Battery
          </button>
        </div>
        <div class="field">
          <button class="btn btn-secondary" onclick="runShell('install')">
            📦 Install APK
          </button>
        </div>
        <div class="field">
          <button class="btn btn-secondary" onclick="runShell('logcat')">
            📜 Logcat
          </button>
        </div>
        <div class="field">
          <button class="btn btn-danger" onclick="runShell('reboot')">
            🔄 Reboot
          </button>
        </div>
      </div>
    </div>
    
    <!-- Output Panel -->
    <div class="card animate-in stagger-6" id="outputPanel" style="display:none">
      <div class="card-header">
        <h3 class="card-title">
          <span>📤</span> Operation Output
        </h3>
        <button class="btn btn-secondary" style="font-size:0.7rem" onclick="document.getElementById('outputPanel').style.display='none'">
          ✕
        </button>
      </div>
      <div class="output-wrap">
        <div class="output-body output-info" id="operationOutput">
          <!-- Output here -->
        </div>
      </div>
    </div>
  </ui:define>
</ui:composition>
