@extends('layouts/admin/base')

@section('content')
<div class="min-h-screen bg-gray-50">
  <!-- Header -->
  @include('admin.partials.header')
  
  <div class="flex">
    <!-- Sidebar -->
    @include('admin.partials.sidebar')
    
    <!-- Main Content -->
    <main class="flex-1 md:ml-64 pt-16 p-6">
      <div class="max-w-7xl mx-auto">
        
        <!-- Page Header -->
        <div class="animate-in stagger-1 mb-8">
          <h1 class="text-3xl font-bold text-gray-100" style="font-family:'Space Grotesk',sans-serif;">Dashboard</h1>
          <p class="text-gray-400 mt-1">Welcome back, Admin. Here's what's happening today.</p>
        </div>
        
        <!-- Stats Grid -->
        <div class="grid-cols-stats">
          <div class="stat-card animate-in stagger-2">
            <div class="stat-value" id="statDevices">0</div>
            <div class="stat-label">Connected Devices</div>
            <div class="stat-change positive">+12% from yesterday</div>
          </div>
          
          <div class="stat-card animate-in stagger-3">
            <div class="stat-value" id="statFlashes">0</div>
            <div class="stat-label">Flashes Today</div>
            <div class="stat-change positive">+8 successful</div>
          </div>
          
          <div class="stat-card animate-in stagger-4">
            <div class="stat-value" id="statApi">0K</div>
            <div class="stat-label">API Requests</div>
            <div class="stat-change positive">+24% increase</div>
          </div>
          
          <div class="stat-card animate-in" style="animation-delay:0.25s">
            <div class="stat-value" id="statUptime">99.9%</div>
            <div class="stat-label">System Uptime</div>
            <div class="stat-change positive">All systems operational</div>
          </div>
        </div>
        
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Device Activity Chart -->
          <div class="chart-container animate-in stagger-2">
            <div class="card-title">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <path d="M8 21h8M12 17v4"/>
              </svg>
              Device Activity
            </div>
            <div class="space-y-4">
              <div class="chart-bar">
                <span class="chart-bar-label">Android 14</span>
                <span class="chart-bar-value">12</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:60%"></div>
                </div>
              </div>
              <div class="chart-bar">
                <span class="chart-bar-label">Android 13</span>
                <span class="chart-bar-value">8</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:40%"></div>
                </div>
              </div>
              <div class="chart-bar">
                <span class="chart-bar-label">Android 12</span>
                <span class="chart-bar-value">5</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:25%"></div>
                </div>
              </div>
              <div class="chart-bar">
                <span class="chart-bar-label">Emulators</span>
                <span class="chart-bar-value">15</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:75%"></div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Flash Operations Chart -->
          <div class="chart-container animate-in stagger-3">
            <div class="card-title">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
              </svg>
              Flash Operations</div>
            <div class="space-y-4">
              <div class="chart-bar">
                <span class="chart-bar-label">System</span>
                <span class="chart-bar-value">45</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:70%;background:var(--accent)"></div>
                </div>
              </div>
              <div class="chart-bar">
                <span class="chart-bar-label">Vendor</span>
                <span class="chart-bar-value">23</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:35%;background:var(--cyan)"></div>
                </div>
              </div>
              <div class="chart-bar">
                <span class="chart-bar-label">Boot</span>
                <span class="chart-bar-value">67</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:85%;background:var(--green)"></div>
                </div>
              </div>
              <div class="chart-bar">
                <span class="chart-bar-label">Recovery</span>
                <span class="chart-bar-value">12</span>
                <div class="chart-bar-track">
                  <div class="chart-bar-fill" style="width:15%;background:var(--yellow)"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Recent Activity Table -->
        <div class="card animate-in stagger-4">
          <div class="card-header">
            <div class="card-title">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
              </svg>
              Recent Activity
            </div>
            <button class="btn btn-secondary btn-sm">View All</button>
          </div>
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Device</th>
                  <th>Operation</th>
                  <th>Status</th>
                  <th>Time</th>
                  <th>Duration</th>
                </tr>
              </thead>
              <tbody id="activityTable">
                <tr>
                  <td class="mono">emulator-5554</td>
                  <td>Flash system.img</td>
                  <td><span class="badge badge-success">Success</span></td>
                  <td class="mono">2 min ago</td>
                  <td class="mono">2m 34s</td>
                </tr>
                <tr>
                  <td class="mono">PIXEL_001</td>
                  <td>ADB shell install</td>
                  <td><span class="badge badge-success">Success</span></td>
                  <td class="mono">15 min ago</td>
                  <td class="mono">45s</td>
                </tr>
                <tr>
                  <td class="mono">emulator-5556</td>
                  <td>Fastboot flash</td>
                  <td><span class="badge badge-warning">Warning</span></td>
                  <td class="mono">1h ago</td>
                  <td class="mono">5m 12s</td>
                </tr>
                <tr>
                  <td class="mono">SM-G998B</td>
                  <td>Logcat capture</td>
                  <td><span class="badge badge-success">Success</span></td>
                  <td class="mono">2h ago</td>
                  <td class="mono">1m 8s</td>
                </tr>
                <tr>
                  <td class="mono">emulator-5558</td>
                  <td>Screen capture</td>
                  <td><span class="badge badge-info">Pending</span></td>
                  <td class="mono">3h ago</td>
                  <td class="mono">--</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card animate-in stagger-5">
          <div class="card-header">
            <div class="card-title">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 6v6l4 2"/>
              </svg>
              Quick Actions
            </div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button class="btn btn-secondary flex flex-col items-center gap-2 p-4">
              <svg class="icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
              </svg>
              Flash Device
            </button>
            <button class="btn btn-secondary flex flex-col items-center gap-2 p-4">
              <svg class="icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <path d="M8 21h8M12 17v4"/>
              </svg>
              Manage Devices
            </button>
            <button class="btn btn-secondary flex flex-col items-center gap-2 p-4">
              <svg class="icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2z"/>
                <path d="M16 13H8"/>
              </svg>
              API Explorer
            </button>
            <button class="btn btn-secondary flex flex-col items-center gap-2 p-4">
              <svg class="icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v6M12 17v6M4.22 4.22l4.24 4.24M15.54 15.54l4.24 4.24M1 12h6M17 12h6M4.22 19.78l4.24-4.24M15.54 8.46l4.24-4.24"/>
              </svg>
              System Tools
            </button>
          </div>
        </div>
        
      </div>
    </main>
  </div>
  
  <!-- Footer -->
  @include('admin.partials.footer')
</div>

<script>
// Animate stat values
function animateStat(elementId, target, suffix = '') {
  const el = document.getElementById(elementId);
  if (!el) return;
  
  let current = 0;
  const increment = target / 50;
  const timer = setInterval(() => {
    current += increment;
    if (current >= target) {
      current = target;
      clearInterval(timer);
    }
    el.textContent = Math.floor(current) + suffix;
  }, 30);
}

// Start animations
setTimeout(() => {
  animateStat('statDevices', 12, '');
  animateStat('statFlashes', 23, '');
  animateStat('statApi', 150, 'K');
}, 300);
</script>
@endsection
