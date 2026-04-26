@extends('flash.layout')

@section('title')
    Samsung Download Mode - Flash Tool
@endsection

@section('content')
<div class="min-h-screen bg-gray-900">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Samsung Download Mode Flasher</h1>
                        <p class="text-blue-100 text-sm">Odin-style firmware flashing for Samsung devices</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span id="deviceStatus" class="px-4 py-2 bg-green-500 bg-opacity-20 text-green-100 rounded-full text-sm font-medium">
                        Disconnected
                    </span>
                    <button onclick="location.href='/flash'" class="px-4 py-2 bg-white bg-opacity-20 text-white rounded-lg hover:bg-opacity-30 transition-all">
                        ← Back to Flash Tool
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Device Info Panel -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Device Information
                    </h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Model:</span>
                            <span id="deviceModel" class="text-white font-medium">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Serial:</span>
                            <span id="deviceSerial" class="text-white font-medium mono">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Mode:</span>
                            <span id="deviceMode" class="text-orange-400 font-medium">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Secure Boot:</span>
                            <span id="deviceSecure" class="text-gray-400">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">BL State:</span>
                            <span id="deviceBL" class="text-gray-400">--</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-400 mb-2">Partition Sizes</h3>
                        <div id="partitionSizes" class="space-y-1 text-sm">
                            <p class="text-gray-500">Connect device to view</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-700">
                        <button onclick="connectSamsung()" id="connectBtn" class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all">
                            Connect Device
                        </button>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-700 mt-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <button onclick="rebootToDownload()" class="w-full py-2 px-4 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium text-sm transition-all flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reboot to Download
                        </button>
                        <button onclick="rebootToNormal()" class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition-all flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Reboot to System
                        </button>
                        <button onclick="checkFirmware()" class="w-full py-2 px-4 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium text-sm transition-all flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Check Firmware
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Flash Operations -->
            <div class="lg:col-span-2">
                <!-- Tab Navigation -->
                <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
                    <div class="border-b border-gray-700">
                        <nav class="flex space-x-4 px-6" aria-label="Tabs">
                            <button onclick="switchSamsungTab('tar-flash')" id="tab-tar-flash" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-blue-400 border-blue-500 focus:outline-none">
                                .tar.md5 Flash
                            </button>
                            <button onclick="switchSamsungTab('partition-flash')" id="tab-partition-flash" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-300 focus:outline-none">
                                Partition Flash
                            </button>
                            <button onclick="switchSamsungTab('pit-flash')" id="tab-pit-flash" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-300 focus:outline-none">
                                PIT Flash
                            </button>
                            <button onclick="switchSamsungTab('firmware-check')" id="tab-firmware-check" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-300 focus:outline-none">
                                Firmware Check
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="p-6">
                        
                        <!-- .tar.md5 Flash Tab -->
                        <div id="tab-content-tar-flash" class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-white mb-2">Flash .tar.md5 Firmware</h3>
                                <p class="text-gray-400 text-sm">Upload and flash Samsung firmware package (Odin-style)</p>
                            </div>
                            
                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Firmware File (.tar.md5)</label>
                                    <div class="flex items-center space-x-4">
                                        <input type="file" id="tarFile" accept=".tar.md5" class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <button onclick="document.getElementById('tarFile').click()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-all">
                                            Browse
                                        </button>
                                    </div>
                                    <p id="selectedFile" class="mt-2 text-sm text-gray-400"></p>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Options</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" id="disableVerify" class="mr-2 bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                                            <span class="text-sm text-gray-300">Disable verification</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" id="continueOnError" class="mr-2 bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                                            <span class="text-sm text-gray-300">Continue on error</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" id="skipBL" class="mr-2 bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                                            <span class="text-sm text-gray-300">Skip bootloader partition</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <button onclick="flashTarMD5()" id="flashTarBtn" class="w-full py-3 px-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                    Start Flash Operation
                                </button>
                            </div>
                            
                            <!-- Progress -->
                            <div id="tarProgress" class="hidden">
                                <h4 class="text-md font-medium text-white mb-3">Flash Progress</h4>
                                <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-400">Overall Progress</span>
                                        <span id="overallProgressText" class="text-sm font-medium text-blue-400">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-800 rounded-full h-2 mb-4">
                                        <div id="overallProgressBar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    
                                    <div id="partitionProgress" class="space-y-3">
                                        <!-- Dynamic partition progress will be added here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Log Output -->
                            <div id="tarLog" class="hidden">
                                <h4 class="text-md font-medium text-white mb-3">Operation Log</h4>
                                <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                    <div id="tarLogContent" class="font-mono text-sm text-gray-300 max-h-60 overflow-y-auto space-y-1">
                                        <!-- Log entries will be added here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Partition Flash Tab -->
                        <div id="tab-content-partition-flash" class="hidden space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-white mb-2">Flash Individual Partition</h3>
                                <p class="text-gray-400 text-sm">Flash a single partition image file</p>
                            </div>
                            
                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Partition</label>
                                        <select id="partitionSelect" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select partition...</option>
                                            <option value="AP">AP (System)</option>
                                            <option value="BL">Bootloader</option>
                                            <option value="CP">CP (Modem)</option>
                                            <option value="CSC">CSC (Carrier)</option>
                                            <option value="HOME">Home</option>
                                            <option value="ODM">ODM</option>
                                            <option value="OXX">OXX</option>
                                            <option value="SYSTEM">SYSTEM</option>
                                            <option value="VENDOR">VENDOR</option>
                                            <option value="PRODUCT">PRODUCT</option>
                                            <option value="USERDATA">USERDATA</option>
                                            <option value="CACHE">CACHE</option>
                                            <option value="RECOVERY">RECOVERY</option>
                                            <option value="BOOT">BOOT</option>
                                            <option value="DTBO">DTBO</option>
                                            <option value="VBMETA">VBMETA</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Image File</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="file" id="partitionImage" accept=".img,.bin,.elf" class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Options</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" id="partitionDisableVerify" class="mr-2 bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                                            <span class="text-sm text-gray-300">Disable verification</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <button onclick="flashPartition()" id="flashPartitionBtn" class="w-full mt-4 py-3 px-4 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg font-medium transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                    Flash Partition
                                </button>
                            </div>
                            
                            <!-- Partition Progress -->
                            <div id="partitionProgressArea" class="hidden">
                                <h4 class="text-md font-medium text-white mb-3">Flash Progress</h4>
                                <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-400">Progress</span>
                                        <span id="partitionProgressText" class="text-sm font-medium text-purple-400">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-800 rounded-full h-2">
                                        <div id="partitionProgressBar" class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PIT Flash Tab -->
                        <div id="tab-content-pit-flash" class="hidden space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-white mb-2">PIT (Partition Information Table)</h3>
                                <p class="text-gray-400 text-sm">⚠️ PIT flashing requires Odin. This feature is limited.</p>
                            </div>
                            
                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700 border-dashed border-yellow-700">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-6 h-6 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-yellow-400 font-medium">Important Notice</h4>
                                        <p class="text-yellow-300 text-sm mt-1">
                                            PIT files cannot be flashed through Fastboot/ADB. You need Odin3 (Windows application) to flash PIT files on Samsung devices.
                                        </p>
                                        <div class="mt-3">
                                            <a href="#" class="text-blue-400 hover:text-blue-300 text-sm font-medium">
                                                Download Odin3 →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <h4 class="text-md font-medium text-white mb-3">Check PIT Information</h4>
                                <p class="text-gray-400 text-sm mb-4">You can view partition information without flashing.</p>
                                
                                <div id="pitInfo" class="bg-gray-800 rounded-lg p-4">
                                    <p class="text-gray-400 text-sm text-center py-4">Connect a Samsung device to view PIT information</p>
                                </div>
                                
                                <button onclick="checkPitInfo()" class="w-full mt-4 py-2 px-4 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium text-sm transition-all">
                                    Check PIT Info
                                </button>
                            </div>
                        </div>
                        
                        <!-- Firmware Check Tab -->
                        <div id="tab-content-firmware-check" class="hidden space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-white mb-2">Firmware Compatibility Check</h3>
                                <p class="text-gray-400 text-sm">Verify firmware compatibility before flashing</p>
                            </div>
                            
                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Firmware File (.tar.md5)</label>
                                    <div class="flex items-center space-x-4">
                                        <input type="file" id="checkFirmwareFile" accept=".tar.md5" class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <button onclick="document.getElementById('checkFirmwareFile').click()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-all">
                                            Browse
                                        </button>
                                    </div>
                                    <p id="checkSelectedFile" class="mt-2 text-sm text-gray-400"></p>
                                </div>
                                
                                <button onclick="verifyFirmware()" id="verifyFirmwareBtn" class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg font-medium transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                    Verify Firmware
                                </button>
                            </div>
                            
                            <!-- Verification Results -->
                            <div id="verificationResults" class="hidden">
                                <h4 class="text-md font-medium text-white mb-3">Verification Results</h4>
                                <div id="verificationContent" class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                    <!-- Results will be displayed here -->
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Operations Log -->
                <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 mt-6">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
                        <h2 class="text-lg font-semibold text-white">Operation Log</h2>
                        <button onclick="clearSamsungLog()" class="text-sm text-gray-400 hover:text-white transition-colors">
                            Clear Log
                        </button>
                    </div>
                    <div id="samsungLog" class="max-h-60 overflow-y-auto p-4 font-mono text-sm">
                        <p class="text-gray-500">No operations yet...</p>
                    </div>
                </div>
            </div>
            
        </div>
    </main>
</div>

<script>
// Samsung Flasher State
let samsungDevice = null;
let isFlashing = false;
let currentOperation = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    checkDeviceConnection();
});

// Check device connection
function checkDeviceConnection() {
    fetch('/api/devices')
        .then(response => response.json())
        .then(result => {
            const devices = result.devices || [];
            const connected = devices.some(d => d.type === 'adb');
            
            if (connected) {
                updateDeviceStatus('Connected', 'connected');
                document.getElementById('connectBtn').textContent = 'Disconnect';
            } else {
                updateDeviceStatus('Disconnected', 'disconnected');
                document.getElementById('connectBtn').textContent = 'Connect Device';
            }
        });
}

// Update device status
function updateDeviceStatus(status, state) {
    const statusEl = document.getElementById('deviceStatus');
    const statusText = document.getElementById('deviceStatus');
    
    statusText.textContent = status;
    
    statusText.className = 'px-4 py-2 rounded-full text-sm font-medium';
    
    if (state === 'connected') {
        statusText.classList.add('bg-green-500', 'bg-opacity-20', 'text-green-100');
    } else if (state === 'download') {
        statusText.classList.add('bg-orange-500', 'bg-opacity-20', 'text-orange-100');
    } else {
        statusText.classList.add('bg-gray-500', 'bg-opacity-20', 'text-gray-100');
    }
}

// Switch Samsung tabs
function switchSamsungTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('[id^="tab-content-"]').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        if (!tab.id.startsWith('tab-content')) {
            tab.classList.remove('border-blue-500', 'text-blue-400');
            tab.classList.add('border-transparent', 'text-gray-400');
        }
    });
    
    // Show selected tab content
    const contentTab = document.getElementById('tab-content-' + tabName);
    if (contentTab) {
        contentTab.classList.remove('hidden');
    }
    
    // Activate selected tab button
    const tabButton = document.getElementById('tab-' + tabName);
    if (tabButton) {
        tabButton.classList.remove('border-transparent', 'text-gray-400');
        tabButton.classList.add('border-blue-500', 'text-blue-400');
    }
}

// Connect/Disconnect device
function connectSamsung() {
    const btn = document.getElementById('connectBtn');
    
    if (btn.textContent === 'Connect Device') {
        // Find connected device
        fetch('/api/devices')
            .then(response => response.json())
            .then(result => {
                const devices = result.devices || [];
                const device = devices.find(d => d.type === 'adb');
                
                if (device) {
                    // Reboot to download mode
                    rebootToDownloadMode(device.id);
                } else {
                    logSamsungMessage('No ADB device connected', 'error');
                }
            });
    } else {
        // Disconnect
        samsungDevice = null;
        document.getElementById('connectBtn').textContent = 'Connect Device';
        updateDeviceStatus('Disconnected', 'disconnected');
        logSamsungMessage('Device disconnected', 'info');
    }
}

// Reboot to download mode
function rebootToDownloadMode(deviceId) {
    logSamsungMessage('Rebooting to download mode...', 'info');
    
    fetch('/api/devices/' + deviceId + '/reboot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: 'bootloader' })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            logSamsungMessage('Rebooted to bootloader', 'success');
            
            // Wait for device to appear in fastboot mode
            setTimeout(() => {
                updateDeviceStatus('Download Mode', 'download');
                document.getElementById('connectBtn').textContent = 'Disconnect';
                logSamsungMessage('Device in download mode', 'success');
                
                // Refresh device info
                loadDeviceInfo();
            }, 5000);
        } else {
            logSamsungMessage('Failed to reboot: ' + (result.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        logSamsungMessage('Error: ' + error.message, 'error');
    });
}

// Reboot to normal mode
function rebootToNormal() {
    if (samsungDevice) {
        logSamsungMessage('Rebooting to normal mode...', 'info');
        
        // Simulate fastboot reboot
        fetch('/api/devices/' + samsungDevice + '/reboot', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mode: 'normal' })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                logSamsungMessage('Rebooting to system...', 'success');
                updateDeviceStatus('Connected', 'connected');
                document.getElementById('connectBtn').textContent = 'Connect Device';
            } else {
                logSamsungMessage('Failed to reboot: ' + (result.error || 'Unknown error'), 'error');
            }
        });
    } else {
        logSamsungMessage('No device connected', 'error');
    }
}

// Load device information
function loadDeviceInfo() {
    // Simulate loading device info
    const deviceInfo = {
        model: 'SM-S918B',
        serial: 'RZ8M50ABCDE',
        mode: 'Download Mode',
        secure: 'Secure',
        blState: 'Locked'
    };
    
    document.getElementById('deviceModel').textContent = deviceInfo.model;
    document.getElementById('deviceSerial').textContent = deviceInfo.serial;
    document.getElementById('deviceMode').textContent = deviceInfo.mode;
    document.getElementById('deviceSecure').textContent = deviceInfo.secure;
    document.getElementById('deviceBL').textContent = deviceInfo.blState;
    
    // Update partition sizes
    const partitionSizesEl = document.getElementById('partitionSizes');
    partitionSizesEl.innerHTML = `
        <div class="flex justify-between text-sm">
            <span class="text-gray-400">AP:</span>
            <span class="text-white">4GB</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-400">BL:</span>
            <span class="text-white">32MB</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-400">CP:</span>
            <span class="text-white">1GB</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-400">CSC:</span>
            <span class="text-white">512MB</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-400">VENDOR:</span>
            <span class="text-white">2GB</span>
        </div>
    `;
}

// Flash .tar.md5 firmware
function flashTarMD5() {
    const fileInput = document.getElementById('tarFile');
    const disableVerify = document.getElementById('disableVerify').checked;
    const continueOnError = document.getElementById('continueOnError').checked;
    const skipBL = document.getElementById('skipBL').checked;
    
    if (!fileInput.files || fileInput.files.length === 0) {
        logSamsungMessage('Please select a firmware file', 'error');
        return;
    }
    
    const file = fileInput.files[0];
    
    if (!file.name.endsWith('.tar.md5')) {
        logSamsungMessage('File must have .tar.md5 extension', 'error');
        return;
    }
    
    logSamsungMessage('Starting flash operation for: ' + file.name, 'info');
    
    // Show progress
    document.getElementById('tarProgress').classList.remove('hidden');
    document.getElementById('tarLog').classList.remove('hidden');
    
    // Simulate flash process
    simulateFlashProcess(file.name);
}

// Simulate flash process
function simulateFlashProcess(filename) {
    const partitions = ['AP', 'BL', 'CP', 'CSC', 'HOME'];
    let currentPartition = 0;
    
    function flashNext() {
        if (currentPartition >= partitions.length) {
            logSamsungMessage('Flash operation completed successfully!', 'success');
            document.getElementById('overallProgressText').textContent = '100%';
            document.getElementById('overallProgressBar').style.width = '100%';
            return;
        }
        
        const partition = partitions[currentPartition];
        const progressEl = document.getElementById('overallProgressBar');
        const progressText = document.getElementById('overallProgressText');
        
        logSamsungMessage('Flashing partition: ' + partition, 'info');
        
        // Add partition progress
        const partitionProgressEl = document.createElement('div');
        partitionProgressEl.id = 'progress-' + partition;
        partitionProgressEl.className = 'flex items-center justify-between text-sm';
        partitionProgressEl.innerHTML = `
            <span class="text-gray-400">${partition}:</span>
            <span class="text-blue-400 font-medium">0%</span>
        `;
        document.getElementById('partitionProgress').appendChild(partitionProgressEl);
        
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 100) progress = 100;
            
            // Update partition progress
            const partProgress = partitionProgressEl.querySelector('span:last-child');
            partProgress.textContent = Math.round(progress) + '%';
            
            // Update overall progress
            const overallProgress = ((currentPartition + progress / 100) / partitions.length) * 100;
            progressText.textContent = Math.round(overallProgress) + '%';
            progressEl.style.width = overallProgress + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                logSamsungMessage('Partition ' + partition + ' flashed successfully', 'success');
                currentPartition++;
                flashNext();
            }
        }, 200);
    }
    
    flashNext();
}

// Flash single partition
function flashPartition() {
    const partition = document.getElementById('partitionSelect').value;
    const fileInput = document.getElementById('partitionImage');
    
    if (!partition) {
        logSamsungMessage('Please select a partition', 'error');
        return;
    }
    
    if (!fileInput.files || fileInput.files.length === 0) {
        logSamsungMessage('Please select an image file', 'error');
        return;
    }
    
    const file = fileInput.files[0];
    
    logSamsungMessage('Flashing ' + partition + ' with ' + file.name, 'info');
    
    // Show progress
    document.getElementById('partitionProgressArea').classList.remove('hidden');
    
    // Simulate progress
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress > 100) progress = 100;
        
        document.getElementById('partitionProgressText').textContent = Math.round(progress) + '%';
        document.getElementById('partitionProgressBar').style.width = progress + '%';
        
        if (progress >= 100) {
            clearInterval(interval);
            logSamsungMessage('Partition ' + partition + ' flashed successfully', 'success');
        }
    }, 300);
}

// Check firmware compatibility
function verifyFirmware() {
    const fileInput = document.getElementById('checkFirmwareFile');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        logSamsungMessage('Please select a firmware file', 'error');
        return;
    }
    
    const file = fileInput.files[0];
    
    if (!file.name.endsWith('.tar.md5')) {
        logSamsungMessage('File must have .tar.md5 extension', 'error');
        return;
    }
    
    logSamsungMessage('Verifying firmware: ' + file.name, 'info');
    
    // Show results
    document.getElementById('verificationResults').classList.remove('hidden');
    
    const resultsEl = document.getElementById('verificationContent');
    resultsEl.innerHTML = `
        <div class="space-y-3">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-400 font-medium">Firmware valid</span>
            </div>
            <div class="border-t border-gray-700 pt-3">
                <h5 class="text-white font-medium mb-2">Firmware Details</h5>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Filename:</span>
                        <span class="text-white">${file.name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Size:</span>
                        <span class="text-white">${(file.size / (1024*1024)).toFixed(2)} MB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Type:</span>
                        <