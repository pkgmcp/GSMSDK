<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/flash.xhtml">
  <ui:define name="content">
    <script type="text/javascript">
      document.title = 'Fastboot Flash | GSMSDK';
    </script>
    
    <div class="animate-in stagger-1">
      <h1 class="text-2xl mb-1" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-0.03em;">
        ⚡ Fastboot Flash Operation
      </h1>
      <p style="color:var(--text2);font-size:0.9rem;">Flash firmware images to device partitions</p>
    </div>
    
    <div class="card animate-in stagger-2">
      <div class="card-header">
        <h3 class="card-title">
          <span>🔧</span> Flash Configuration
        </h3>
        <span class="badge-info" id="deviceBadge">Device: none</span>
      </div>
      
      <div class="section-title">
        <span style="font-size:0.75rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;">STEP 1: Configure Partition</span>
        <div class="section-title-line"></div>
      </div>
      
      <div class="field-row">
        <div class="field" style="flex:2">
          <label class="field-label">Partition Name</label>
          <select class="field-select" id="partitionName">
            <option value="">-- Select partition --</option>
            <option value="boot">boot</option>
            <option value="system">system</option>
            <option value="vendor">vendor</option>
            <option value="product">product</option>
            <option value="system_ext">system_ext</option>
            <option value="recovery">recovery</option>
            <option value="vbmeta">vbmeta</option>
          </select>
        </div>
        <div class="field" style="flex:1">
          <label class="field-label">Slot (A/B)</label>
          <select class="field-select" id="slotSelect">
            <option value="all">All slots</option>
            <option value="a">Slot A</option>
            <option value="b">Slot B</option>
          </select>
        </div>
      </div>
      
      <div class="section-title">
        <span style="font-size:0.75rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;">STEP 2: Select Image</span>
        <div class="section-title-line"></div>
      </div>
      
      <div class="field-row">
        <div class="field" style="flex:3">
          <label class="field-label">Image File</label>
          <input type="text" class="field-input mono" id="imagePath" placeholder="/path/to/image.img"/>
        </div>
        <div class="field" style="flex:1">
          <label class="field-label">Image Size</label>
          <input type="text" class="field-input mono" id="imageSize" placeholder="Auto" disabled/>
        </div>
        <div class="field" style="flex:1;display:flex;align-items:flex-end;">
          <button class="btn btn-secondary" style="width:100%" onclick="document.getElementById('filePicker').click()">
            📁 Browse
          </button>
          <input type="file" id="filePicker" style="display:none" onchange="handleFileSelect(event)"/>
        </div>
      </div>
      
      <div class="section-title">
        <span style="font-size:0.75rem;color:var(--text3);text-transform:uppercase;letter-spacing:0.1em;">STEP 3: Flash Options</span>
        <div class="section-title-line"></div>
      </div>
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div>
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
            <input type="checkbox" id="forceFlash" style="width:16px;height:16px;accent-color:var(--accent);"/>
            Force flash (ignore lock)
          </label>
        </div>
        <div>
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
            <input type="checkbox" id="sparseConvert" style="width:16px;height:16px;accent-color:var(--accent);"/>
            Convert to sparse image
          </label>
        </div>
        <div>
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
            <input type="checkbox" id="verifyFlash" style="width:16px;height:16px;accent-color:var(--accent);" checked/>
            Verify after flash
          </label>
        </div>
        <div>
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
            <input type="checkbox" id="lockAfterFlash" style="width:16px;height:16px;accent-color:var(--accent);"/>
            Lock bootloader after
          </label>
        </div>
      </div>
      
      <div class="btn-group" style="margin-top:0.5rem">
        <button class="btn btn-primary" onclick="startFlash()" id="flashBtn">
          <span id="flashBtnText">⚡ Start Flash</span>
          <span class="spinner sm" id="flashSpinner" style="display:none"></span>
        </button>
        <button class="btn btn-secondary" onclick="clearOperation()">
          🧹 Clear
        </button>
        <button class="btn btn-secondary" onclick="validateImage()">
          ✅ Validate
        </button>
      </div>
    </div>
    
    <!-- Progress -->
    <div class="card animate-in" style="animation-delay:0.15s" id="progressCard" style="display:none">
      <div class="card-header">
        <h3 class="card-title">
          <span>📊</span> Flash Progress
        </h3>
        <span class="badge" id="flashStatus">Pending</span>
      </div>
      <div style="margin-bottom:1rem">
        <div style="display:flex;justify-content:space-between;margin-bottom:0.25rem;font-size:0.75rem;color:var(--text2)">
          <span>Uploading...</span>
          <span id="progressPercent">0%</span>
        </div>
        <div class="progress-wrap">
          <div class="progress-bar" id="progressBar" style="width:0%"></div>
        </div>
      </div>
      <div class="output-wrap">
        <div class="output-header">
          <span class="output-header-text">Flash Log</span>
        </div>
        <div class="output-body output-ok" id="flashLog">
Waiting to start...
        </div>
      </div>
    </div>
    
    <!-- Operation Steps -->
    <div class="card animate-in" style="animation-delay:0.2s">
      <div class="card-header">
        <h3 class="card-title">
          <span>📋</span> Flash Steps
        </h3>
      </div>
      
      <div class="flash-step" id="step1">
        <div class="flash-step-num">1</div>
        <div class="flash-step-content">
          <h4>Initialize Fastboot Connection</h4>
          <p>Connect to device in fastboot mode and verify protocol version</p>
        </div>
      </div>
      
      <div class="flash-step" id="step2">
        <div class="flash-step-num">2</div>
        <div class="flash-step-content">
          <h4>Erase Partition</h4>
          <p>Clean target partition before writing new data</p>
        </div>
      </div>
      
      <div class="flash-step" id="step3">
        <div class="flash-step-num">3</div>
        <div class="flash-step-content">
          <h4>Upload Image</h4>
          <p>Transfer image to device using sparse image protocol</p>
        </div>
      </div>
      
      <div class="flash-step" id="step4">
        <div class="flash-step-num">4</div>
        <div class="flash-step-content">
          <h4>Verify Integrity</h4>
          <p>Compute and verify SHA256 checksum</p>
        </div>
      </div>
      
      <div class="flash-step" id="step5">
        <div class="flash-step-num">5</div>
        <div class="flash-step-content">
          <h4>Reboot Device</h4>
          <p>Restart device into updated partition</p>
        </div>
      </div>
    </div>
    
    <!-- Recent Operations -->
    <div class="card animate-in" style="animation-delay:0.25s">
      <div class="card-header">
        <h3 class="card-title">
          <span>📜</span> Recent Operations
        </h3>
        <button class="btn btn-secondary" style="font-size:0.7rem;padding:0.3rem 0.6rem;">View All</button>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Time</th>
              <th>Partition</th>
              <th>Size</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="mono">14:32:15</td>
              <td>boot</td>
              <td>32.0 MB</td>
              <td><span class="badge badge-success">Success</span></td>
            </tr>
            <tr>
              <td class="mono">12:05:43</td>
              <td>recovery</td>
              <td>64.0 MB</td>
              <td><span class="badge badge-success">Success</span></td>
            </tr>
            <tr>
              <td class="mono">09:21:08</td>
              <td>system</td>
              <td>1.2 GB</td>
              <td><span class="badge badge-success">Success</span></td>
            </tr>
            <tr>
              <td class="mono">--:--:--</td>
              <td>vendor</td>
              <td>--</td>
              <td><span class="badge">Pending</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </ui:define>
</ui:composition>
