<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/user/base.gsm.php">
  <ui:define name="title">User Panel</ui:define>
  <ui:define name="content">
    
    @include('user.partials.sidebar')
    
    <div class="main-content">
      @include('user.partials.header')
      
      <div class="p-8">
        <!-- Profile Card -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Profile Settings</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
              <div class="avatar-lg mx-auto mb-4">
                <span class="text-4xl">A</span>
              </div>
              <button class="btn btn-secondary">Change Avatar</button>
            </div>
            <div class="md:col-span-2 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="field-label">Full Name</label>
                  <input type="text" class="field-input" value="Admin User">
                </div>
                <div>
                  <label class="field-label">Email</label>
                  <input type="email" class="field-input" value="admin@example.com">
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="field-label">Role</label>
                  <select class="field-select">
                    <option>Super Admin</option>
                  </select>
                </div>
                <div>
                  <label class="field-label">Status</label>
                  <span class="badge badge-success">Active</span>
                </div>
              </div>
              <button class="btn btn-primary">Save Changes</button>
            </div>
          </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>Action</th>
                  <th>Target</th>
                  <th>IP Address</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Login</td>
                  <td>Browser</td>
                  <td class="mono">192.168.1.100</td>
                  <td class="mono">2 min ago</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </ui:define>
</ui:composition>
