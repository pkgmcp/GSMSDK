@extends('layouts.admin.base')

@section('title')
    Firmware Management
@endsection

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Firmware Management</h1>
        <p class="text-gray-400">Manage and sync firmware for all devices</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-500 bg-opacity-20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                </div>
                <span class="text-sm text-gray-400">Total Firmware</span>
            </div>
            <div class="text-2xl font-bold text-white">{{ $totalFirmware }}</div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-green-500 bg-opacity-20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <span class="text-sm text-gray-400">Popular</span>
            </div>
            <div class="text-2xl font-bold text-white">{{ $popularFirmware }}</div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-yellow-500 bg-opacity-20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <span class="text-sm text-gray-400">Total Downloads</span>
            </div>
            <div class="text-2xl font-bold text-white">{{ number_format($totalDownloads) }}</div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-500 bg-opacity-20 rounded-lg">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <span class="text-sm text-gray-400">Brands</span>
            </div>
            <div class="text-2xl font-bold text-white">{{ $totalBrands }}</div>
        </div>
    </div>

    <!-- Sync Controls -->
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-white">External Sync</h2>
            <button onclick="syncFirmware()" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 transition-all">
                Sync All Sources
            </button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-900 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">📱</div>
                <div class="font-medium text-white">Mifirm</div>
                <div class="text-sm text-gray-400">Xiaomi</div>
            </div>
            <div class="bg-gray-900 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">📱</div>
                <div class="font-medium text-white">SamFw</div>
                <div class="text-sm text-gray-400">Samsung</div>
            </div>
            <div class="bg-gray-900 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">📄</div>
                <div class="font-medium text-white">FirmwareFile</div>
                <div class="text-sm text-gray-400">Generic</div>
            </div>
            <div class="bg-gray-900 rounded-lg p-4 text-center">
                <div class="text-2xl mb-2">🍎</div>
                <div class="font-medium text-white">iPSW.me</div>
                <div class="text-sm text-gray-400">Apple</div>
            </div>
        </div>
        <div id="syncStatus" class="mt-4 hidden">
            <div class="flex items-center gap-2 text-green-400">
                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Syncing firmware data...</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-4 mb-6">
        <a href="/admin/firmware/create" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all">
            + Add Firmware
        </a>
        <button onclick="refreshList()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-all">
            ↻ Refresh List
        </button>
    </div>

    <!-- Firmware Table -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-900">
                <tr>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Brand</th>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Model</th>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Version</th>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Type</th>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Size</th>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Downloads</th>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Status</th>
                    <th class="text-left p-4 text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody id="firmwareTable">
                @foreach($firmwareList as $fw)
                <tr class="border-t border-gray-700 hover:bg-gray-700/50 transition-all">
                    <td class="p-4">
                        <span class="font-medium text-white">{{ $fw['brand'] }}</span>
                    </td>
                    <td class="p-4 text-white">{{ $fw['model'] }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-blue-500 bg-opacity-20 text-blue-400 rounded text-sm font-medium">
                            {{ $fw['version'] }}
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-sm font-medium
                            @if($fw['type'] === 'official') bg-green-500 bg-opacity-20 text-green-400
                            @elseif($fw['type'] === 'beta') bg-yellow-500 bg-opacity-20 text-yellow-400
                            @else bg-gray-500 bg-opacity-20 text-gray-400 @endif">
                            {{ ucfirst($fw['type']) }}
                        </span>
                    </td>
                    <td class="p-4 text-gray-300">{{ $fw['size'] }}</td>
                    <td class="p-4 text-white">{{ number_format($fw['downloads']) }}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-sm font-medium
                            @if($fw['status'] === 'active') bg-green-500 bg-opacity-20 text-green-400
                            @elseif($fw['status'] === 'beta') bg-yellow-500 bg-opacity-20 text-yellow-400
                            @else bg-red-500 bg-opacity-20 text-red-400 @endif">
                            {{ ucfirst($fw['status']) }}
                        </span>
                    </td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            <a href="/admin/firmware/{{ $fw['id'] }}/edit" class="p-2 bg-blue-500 bg-opacity-20 text-blue-400 rounded hover:bg-blue-500 hover:bg-opacity-30 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button onclick="deleteFirmware({{ $fw['id'] }})" class="p-2 bg-red-500 bg-opacity-20 text-red-400 rounded hover:bg-red-500 hover:bg-opacity-30 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-6">
        <nav class="flex gap-2">
            <a href="#" class="px-4 py-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-all text-white">‹</a>
            <a href="#" class="px-4 py-2 bg-blue-600 rounded-lg text-white">1</a>
            <a href="#" class="px-4 py-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-all text-white">2</a>
            <a href="#" class="px-4 py-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-all text-white">3</a>
            <a href="#" class="px-4 py-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-all text-white">›</a>
        </nav>
    </div>
</div>

<script>
function syncFirmware() {
    const status = document.getElementById('syncStatus');
    status.classList.remove('hidden');
    
    fetch('/api/firmware/sync', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        status.classList.add('hidden');
        alert('Sync completed: ' + JSON.stringify(data));
        refreshList();
    })
    .catch(error => {
        status.classList.add('hidden');
        alert('Sync failed: ' + error.message);
    });
}

function refreshList() {
    location.reload();
}

function deleteFirmware(id) {
    if (!confirm('Delete this firmware?')) return;
    
    fetch('/api/firmware/' + id, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Deleted successfully');
            refreshList();
        }
    })
    .catch(error => alert('Delete failed: ' + error.message));
}
</script>
@endsection
