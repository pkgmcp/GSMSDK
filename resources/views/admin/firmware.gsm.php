@extends('layouts.admin.base')

@section('title')
    Firmware Management - All Brands & Devices
@endsection

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Firmware Management Hub</h1>
                <p class="text-gray-400">Complete firmware repository with IMEI repair, FRP removal, and all major brands</p>
            </div>
            <div class="flex gap-3">
                <button onclick="syncFirmware()" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Sync External
                </button>
                <a href="/admin/firmware/create" class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-medium hover:from-green-700 hover:to-emerald-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Firmware
                </a>
            </div>
        </div>

        <!-- Sync Status -->
        <div id="syncStatus" class="hidden p-4 rounded-lg bg-blue-500 bg-opacity-20 border border-blue-500 mb-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span class="text-blue-100">Syncing firmware from external sources...</span>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8">
        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-blue-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-blue-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">Total</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statTotal">0</div>
            <div class="text-xs text-gray-500 mt-1">Firmware Files</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-green-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-green-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">Active</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statActive">0</div>
            <div class="text-xs text-gray-500 mt-1">Ready to Download</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-yellow-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-yellow-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">Popular</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statPopular">0</div>
            <div class="text-xs text-gray-500 mt-1">Top Downloads</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-purple-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-purple-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">Brands</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statBrands">0</div>
            <div class="text-xs text-gray-500 mt-1">Supported</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-red-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-red-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">IMEI Repair</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statImei">0</div>
            <div class="text-xs text-gray-500 mt-1">Supported</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-orange-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-orange-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">FRP Remove</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statFrpr">0</div>
            <div class="text-xs text-gray-500 mt-1">Supported</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-cyan-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-cyan-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">Total DL</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statDownloads">0</div>
            <div class="text-xs text-gray-500 mt-1">Downloads</div>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-emerald-500/50 transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-emerald-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium uppercase">Rating</span>
            </div>
            <div class="text-2xl font-bold text-white" id="statRating">0.0</div>
            <div class="text-xs text-gray-500 mt-1">Avg Stars</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-400 mb-2">Search</label>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Brand, model, version..." class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <svg class="w-5 h-5 text-gray-500 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-400 mb-2">Brand</label>
                <select id="brandFilter" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">All Brands</option>
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-400 mb-2">Model</label>
                <input type="text" id="modelFilter" placeholder="Model name..." class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-400 mb-2">Firmware Type</label>
                <select id="typeFilter" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">All Types</option>
                    <option value="official">Official</option>
                    <option value="hyperos">HyperOS</option>
                    <option value="beta">Beta</option>
                    <option value="stock">Stock</option>
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-400 mb-2">Android</label>
                <select id="androidFilter" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">All Versions</option>
                    <option value="15">Android 15</option>
                    <option value="14">Android 14</option>
                    <option value="13">Android 13</option>
                    <option value="12">Android 12</option>
                    <option value="11">Android 11</option>
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-400 mb-2">Security Patch</label>
                <select id="patchFilter" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">All Patches</option>
                    <option value="2026-01-01">2026+</option>
                    <option value="2025-12-01">Dec 2025+</option>
                    <option value="2025-10-01">Oct 2025+</option>
                    <option value="2025-09-01">Sep 2025+</option>
                    <option value="2025-06-01">Jun 2025+</option>
                    <option value="2025-03-01">Mar 2025+</option>
                    <option value="2025-01-01">2025+</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button onclick="applyFilters()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <button onclick="resetFilters()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-all">Reset</button>
            </div>
        </div>

        <!-- Feature Toggles -->
        <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-gray-700">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="imeiToggle" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500" onchange="applyFilters()">
                <span class="text-sm text-gray-300">IMEI Repair</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="frpToggle" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500" onchange="applyFilters()">
                <span class="text-sm text-gray-300">FRP Remove</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="otaToggle" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500" onchange="applyFilters()">
                <span class="text-sm text-gray-300">OTA Supported</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="popularToggle" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500" onchange="applyFilters()">
                <span class="text-sm text-gray-300">Popular Only</span>
            </label>
        </div>
    </div>

    <!-- Results Count -->
    <div class="flex items-center justify-between mb-4">
        <div class="text-gray-400">
            Showing <span id="resultCount" class="text-white font-medium">0</span> of <span id="totalCount" class="text-white font-medium">0</span> firmware files
        </div>
        <div class="flex gap-2">
            <button onclick="sortTable('created_at', 'desc')" class="px-3 py-1 text-sm bg-gray-700 rounded hover:bg-gray-600 transition-all">Latest</button>
            <button onclick="sortTable('download_count', 'desc')" class="px-3 py-1 text-sm bg-gray-700 rounded hover:bg-gray-600 transition-all">Most DL</button>
            <button onclick="sortTable('version', 'desc')" class="px-3 py-1 text-sm bg-gray-700 rounded hover:bg-gray-600 transition-all">Version</button>
        </div>
    </div>

    <!-- Firmware Table -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-900">
                    <tr>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Brand</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Model</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Version</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Build</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Security</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Android</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Size</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">DLs</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Rating</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Features</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left p-4 text-sm font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="firmwareTable" class="divide-y divide-gray-800">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="flex justify-center mt-6 gap-2"></div>

    <!-- Loading -->
    <div id="loading" class="text-center py-12">
        <div class="inline-flex items-center gap-3 text-gray-400">
            <svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Loading firmware database...
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-xl border border-gray-700 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Edit Firmware</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="editForm" class="p-6 space-y-4" onsubmit="saveFirmware(event)">
            <input type="hidden" id="editId">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Brand *</label>
                    <input type="text" id="editBrand" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Model *</label>
                    <input type="text" id="editModel" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Version *</label>
                    <input type="text" id="editVersion" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Build Number</label>
                    <input type="text" id="editBuildNumber" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Security Patch Date</label>
                    <input type="date" id="editSecurityPatch" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Android Version</label>
                    <input type="text" id="editAndroidVersion" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Firmware Type</label>
                <select id="editType" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="official">Official</option>
                    <option value="hyperos">HyperOS</option>
                    <option value="beta">Beta</option>
                    <option value="stock">Stock</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Region</label>
                <input type="text" id="editRegion" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="e.g., WW, CN, EU, IN">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">File Name *</label>
                    <input type="text" id="editFileName" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">File Size</label>
                    <input type="text" id="editFileSize" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="e.g., 1.5GB">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">SHA256 Hash</label>
                <input type="text" id="editFileHash" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Download URL</label>
                <input type="url" id="editDownloadUrl" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Changelog</label>
                <textarea id="editChangelog" rows="3" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none" placeholder="+ Change 1\n+ Change 2"></textarea>
            </div>

            <!-- Feature Flags -->
            <div class="border-t border-gray-700 pt-4">
                <h4 class="text-sm font-semibold text-gray-400 mb-3">Feature Flags</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editImei" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-300">IMEI Repair</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editFrpr" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-300">FRP Remove</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editCameraSms" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-300">Camera/SMS OK</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editOta" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-300">OTA Supported</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editFlashMode" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500" checked>
                        <span class="text-sm text-gray-300">Flash Mode</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editAdbMode" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500" checked>
                        <span class="text-sm text-gray-300">ADB Mode</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editPopular" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-300">Popular</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="editRecommended" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-300">Recommended</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-all">Save Changes</button>
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let firmwareData = [];
let filteredData = [];
let currentPage = 1;
const itemsPerPage = 50;

// Initialize
async function init() {
    await loadFirmware();
    await loadStats();
}

// Load firmware from API
async function loadFirmware() {
    try {
        const response = await fetch('/api/firmware?limit=500');
        const data = await response.json();
        firmwareData = data.firmware || [];
        applyFilters();
        document.getElementById('loading').classList.add('hidden');
    } catch (error) {
        console.error('Failed to load firmware:', error);
        document.getElementById('loading').innerHTML = '<div class="text-red-400 py-12 text-center">Failed to load firmware database</div>';
    }
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch('/api/firmware/statistics');
        const data = await response.json();
        const stats = data.statistics;
        
        document.getElementById('statTotal').textContent = stats.total_firmware;
        document.getElementById('statActive').textContent = stats.active_firmware;
        document.getElementById('statPopular').textContent = stats.popular_firmware;
        document.getElementById('statBrands').textContent = stats.brands;
        document.getElementById('statImei').textContent = stats.firmware.filter(f => f.imei_repair_supported).length;
        document.getElementById('statFrpr').textContent = stats.firmware.filter(f => f.frp_remove_supported).length;
        document.getElementById('statDownloads').textContent = stats.total_downloads.toLocaleString();
        
        const avgRating = stats.firmware.reduce((sum, f) => sum + f.rating, 0) / stats.firmware.length}