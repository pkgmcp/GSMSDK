<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/flash.xhtml">
  <ui:define name="content">
    <script type="text/javascript">
      document.title = 'Flash Dashboard | GSMSDK';
    </script>
    
    <div class="animate-in stagger-1">
      <h1 class="text-2xl mb-1" style="font-family:'Space Grotesk',sans-serif;font-weight:700;letter-spacing:-0.03em;">
        ⚡ Fastboot Flash Center
      </h1>
      <p style="color:var(--text2);font-size:0.9rem;">Manage device partitions and flash firmware images</p>
    </div>
    
    <!-- Quick Stats Banner -->
    <div class="banner animate-in stagger-2">
      <div class="banner-icon">📱</div>
      <div class="banner-content">
        <h3>Device Connected: emulator-5554</h3>
        <p>Android 14 | API 34 | Google APIs | x86_64</p>
      </div>
    </div>
    
    <!-- Stat Grid -->
    <div class="card-grid animate-in stagger-3">
      <div class="stat-card">
        <div class="stat-value" id="slotCount">0</div>
        <div class="stat-label">Active Slots</div>
      </div>
      <div class="stat-card">
        <div class="stat-value" id="partitionCount">0</div>
        <div class="stat-label">Partitions</div>
      </div>
      <div class="stat-card">
        <div class="stat-value" id="flashCount">0</div>
        <div class="stat-label">Flashes Today</div>
      </div>
      <div class="stat-card">
        <div class="stat-value" id="successRate">100%</div>
        <div class="stat-label">Success Rate</div>
      </div>
    </div>
    
    <!-- Device Info Card -->
    <div class="card animate-in stagger-4">
      <div class="card-header">
        <h3 class="card-title">
          <span>📱</span> Device Information
        </h3>
        <div class="device-indicator">
          <span class="device-indicator-dot online"></span>
          <span>Online</span>
        </div>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Property</th>
              <th>Value</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="deviceInfoBody">
            <tr>
              <td colspan="3" style="text-align:center;padding:2rem;color:var(--text3);">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Partition Table -->
    <div class="card animate-in" style="animation-delay:0.25s">
      <div class="card-header">
        <h3 class="card-title">
          <span>💾</span> Partition Management
        </h3>
        <div class="btn-group">
          <button class="btn btn-secondary" onclick="refreshPartitions()">
            ↻ Refresh
          </button>
          <button class="btn btn-primary" onclick="navigateTo('flash')">
            ⚡ New Flash
          </button>
        </div>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Partition</th>
              <th>Type</th>
              <th>Size</th>
              <th>Slot</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="partitionTableBody">
            <!-- Populated by JS -->
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card animate-in" style="animation-delay:0.3s">
      <div class="card-header">
        <h3 class="card-title">
          <span>⚡</span> Quick Actions
        </h3>
      </div>
      <div class="btn-group" style="flex-wrap:wrap">
        <button class="btn btn-secondary" onclick="rebootDevice()">
          🔄 Reboot
        </button>
        <button class="btn btn-secondary" onclick="rebootBootloader()">
          🔧 Bootloader
        </button>
        <button class="btn btn-secondary" onclick="rebootRecovery()">
          🚑 Recovery
        </button>
        <button class="btn btn-secondary" onclick="lockBootloader()">
          🔒 Lock BL
        </button>
        <button class="btn btn-secondary" onclick="unlockBootloader()">
          🔓 Unlock BL
        </button>
        <button class="btn btn-danger" onclick="factoryReset()">
          💣 Factory Reset
        </button>
      </div>
    </div>
  </ui:define>
</ui:composition>
