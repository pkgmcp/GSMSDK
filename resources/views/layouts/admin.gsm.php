<?xml version="1.0" encoding="UTF-8"?>
<ui:composition xmlns:ui="http://xmlns.jcp.org/jsf/facelets"
                template="/layouts/admin/base.gsm.php">
  <ui:define name="title">Admin Dashboard | GSMSDK</ui:define>
  <ui:define name="content">
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      @include('admin.partials.header')
      
      <div class="flex">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')
        
        <!-- Main Content -->
        <main class="flex-1 md:ml-64 pt-16 p-6">
          @yield('content')
        </main>
      </div>
      
      <!-- Footer -->
      @include('admin.partials.footer')
    </div>
  </ui:define>
</ui:composition>
