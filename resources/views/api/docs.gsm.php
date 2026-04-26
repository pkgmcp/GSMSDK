<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/admin/base.gsm.php">
  <ui:define name="title">API Documentation</ui:define>
  <ui:define name="content">
    
    @include('admin.partials.header')
    
    <div class="flex">
      @include('admin.partials.sidebar')
      
      <main class="flex-1 md:ml-64 pt-16 p-6">
        <div class="max-w-7xl mx-auto">
          
          <!-- Introduction -->
          <div class="card animate-in">
            <div class="card-header">
              <div class="card-title">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                </svg>
                API Reference
              </div>
              <span class="badge badge-accent">v2.0.0</span>
            </div>
            <p class="mb-4">GSMSDK provides a comprehensive RESTful API for managing Android devices, firmware flashing, and system operations.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                <div class="text-cyan-400 mb-2">
                  <svg class="icon-lg inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                  </svg>
                </div>
                <div class="font-semibold mb-1">Base URL</div>
                <code class="text-sm text-gray-400">https://api.gsmsdk.io/v2</code>
              </div>
              
              <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                <div class="text-green-400 mb-2">
                  <svg class="icon-lg inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4"/>
                  </svg>
                </div>
                <div class="font-semibold mb-1">Format</div>
                <code class="text-sm text-gray-400">JSON</code>
              </div>
              
              <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                <div class="text-yellow-400 mb-2">
                  <svg class="icon-lg inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                  </svg>
                </div>
                <div class="font-semibold mb-1">Auth</div>
                <code class="text-sm text-gray-400">Bearer Token</code>
              </div>
            </div>
          </div>
          
          <!-- Authentication -->
          <div class="card animate-in stagger-2">
            <div class="card-header">
              <h3 class="card-title">Authentication</h3>
            </div>
            <p class="mb-4">All write operations require authentication via Bearer token.</p>
            
            <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700 mb-4">
              <div class="text-sm text-gray-400 mb-2">Example Request</div>
              <pre class="mono text-sm text-gray-300 overflow-x-auto">GET /api/devices
Authorization: Bearer YOUR_API_TOKEN</pre>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                <h4 class="font-semibold mb-2">Login</h4>
                <pre class="mono text-sm text-gray-300 overflow-x-auto">POST /api/auth/login
{
  "email": "user@example.com",
  "password": "secret"
}</pre>
              </div>
              
              <div class="p-4 bg-gray-800/50 rounded-lg border border-gray-700">
                <h4 class="font-semibold mb-2">Response</h4>
                <pre class="mono text-sm text-gray-300 overflow-x-auto">{
  "status": "success",
  "token": "eyJhbGc...",
  "expires_in": 3600
}</pre>
              </div>
            </div>
          </div>
          
          <!-- Endpoints -->
          <div class="card animate-in stagger-3">
            <div class="card-header">
              <h3 class="card-title">Endpoints</h3>
            </div>
            
            <!-- Device Endpoints -->
            <div class="border-b border-gray-700 pb-4 mb-4">
              <h4 class="text-lg font-semibold mb-3 text-purple-400">Device Management</h4>
              
              <div class="space-y-3">
                <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-green-400 font-mono text-sm">GET</span>
                    <span class="badge badge-info">Authenticated</span>
                  </div>
                  <code class="text-gray-300">/api/devices</code>
                  <p class="text-sm text-gray-400 mt-1">List all connected devices</p>
                </div>
                
                <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-blue-400 font-mono text-sm">POST</span>
                    <span class="badge badge-info">Authenticated</span>
                  </div>
                  <code class="text-gray-300">/api/devices/{id}/shell</code>
                  <p class="text-sm text-gray-400 mt-1">Execute shell command</p>
                </div>
              </div>
            </div>
            
            <!-- Flash Endpoints -->
            <div class="border-b border-gray-700 pb-4 mb-4">
              <h4 class="text-lg font-semibold mb-3 text-orange-400">Flash Operations</h4>
              
              <div class="space-y-3">
                <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-red-400 font-mono text-sm">POST</span>
                    <span class="badge badge-info">Authenticated</span>
                  </div>
                  <code class="text-gray-300">/api/flash</code>
                  <p class="text-sm text-gray-400 mt-1">Flash firmware to partition</p>
                </div>
                
                <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-green-400 font-mono text-sm">GET</span>
                    <span class="badge badge-info">Authenticated</span>
                  </div>
                  <code class="text-gray-300">/api/flash/history</code>
                  <p class="text-sm text-gray-400 mt-1">Get flash history</p>
                </div>
              </div>
            </div>
            
            <!-- ADB Endpoints -->
            <div>
              <h4 class="text-lg font-semibold mb-3 text-cyan-400">ADB Commands</h4>
              
              <div class="space-y-3">
                <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-purple-400 font-mono text-sm">POST</span>
                    <span class="badge badge-info">Authenticated</span>
                  </div>
                  <code class="text-gray-300">/api/adb/install</code>
                  <p class="text-sm text-gray-400 mt-1">Install APK on device</p>
                </div>
                
                <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-purple-400 font-mono text-sm">POST</span>
                    <span class="badge badge-info">Authenticated</span>
                  </div>
                  <code class="text-gray-300">/api/adb/logcat</code>
                  <p class="text-sm text-gray-400 mt-1">Get logcat output</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Rate Limiting -->
          <div class="card animate-in stagger-4">
            <div class="card-header">
              <h3 class="card-title">Rate Limiting</h3>
            </div>
            <p class="mb-4">API requests are limited to <strong>60 requests per minute</strong> per IP address.</p>
            
            <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-300">Rate Limit Headers</span>
              </div>
              <pre class="mono text-sm text-gray-300 overflow-x-auto">X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1640995200</pre>
            </div>
          </div>
          
          <!-- Error Codes -->
          <div class="card animate-in stagger-5">
            <div class="card-header">
              <h3 class="card-title">Error Codes</h3>
            </div>
            
            <div class="overflow-x-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Meaning</th>
                    <th>Solution</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="mono text-red-400">400</td>
                    <td>Bad Request</td>
                    <td>Check request parameters</td>
                  </tr>
                  <tr>
                    <td class="mono text-red-400">401</td>
                    <td>Unauthorized</td>
                    <td>Provide valid API token</td>
                  </tr>
                  <tr>
                    <td class="mono text-red-400">403</td>
                    <td>Forbidden</td>
                    <td>Check permissions</td>
                  </tr>
                  <tr>
                    <td class="mono text-red-400">404</td>
                    <td>Not Found</td>
                    <td>Verify endpoint URL</td>
                  </tr>
                  <tr>
                    <td class="mono text-red-400">429</td>
                    <td>Too Many Requests</td>
                    <td>Wait and retry</td>
                  </tr>
                  <tr>
                    <td class="mono text-red-400">500</td>
                    <td>Internal Server Error</td>
                    <td>Contact support</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
        </div>
      </main>
    </div>
    
    @include('admin.partials.footer')
  </ui:define>
</ui:composition>
