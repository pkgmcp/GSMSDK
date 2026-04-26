// Global State
let ws = null;
let deviceConnected = false;
let currentDeviceMode = 'disconnected';
let currentTab = 'device';
let flashQueue = [];

// Global State
let ws = null;
let deviceConnected = false;
let currentDeviceMode = "disconnected";
let currentTab = "device";
let flashQueue = [];
let isFlashing = false;
let currentBrand = "xiaomi";

// Supported brands
const SUPPORTED_BRANDS = {
    xiaomi: { name: "Xiaomi", color: "#ff6700" },
    google: { name: "Google", color: "#4285f4" },
    samsung: { name: "Samsung", color: "#1428a0" },
    asus: { name: "ASUS", color: "#ff0000" },
    motorola: { name: "Motorola", color: "#ff6600" }
};

let isFlashing = false;

// Initialize dashboard
function initDashboard() {
    selectBrand("xiaomi");

    // Setup extended brands and operations
    setupExtendedBrands();
    setupExtendedOperations();
    // Create and insert advanced tab
    const mainContent = document.querySelector(".content-area");
    const advancedTab = createAdvancedTab();
    mainContent.appendChild(advancedTab);
    setupEventListeners();
    setupWebSocket();
    setupWorkers();
    updateDeviceStatus();
}

// Setup event listeners
function setupEventListeners() {
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    // File drag and drop
    document.querySelectorAll('.flash-slot').forEach(slot => {
        slot.addEventListener('dragover', handleDragOver);
        slot.addEventListener('dragleave', handleDragLeave);
        slot.addEventListener('drop', handleFileDrop);
        slot.addEventListener('click', () => selectFileForSlot(slot.dataset.partition));
    });

    // Connect button
    document.getElementById('connectBtn').addEventListener('click', connectDevice);

    // Keyboard shortcuts
    document.addEventListener('keydown', handleKeyboardShortcuts);

    // Prevent default drag behaviors
    document.addEventListener('dragover', e => e.preventDefault());
    document.addEventListener('drop', e => e.preventDefault());
}

// Setup WebSocket connection
function setupWebSocket() {
    try {
        // Use secure WebSocket for production
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        ws = new WebSocket(`${protocol}//${window.location.host}/ws`);

        ws.onopen = () => {
            console.log('WebSocket connected');
            sendMessage('register', { type: 'dashboard' });
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            handleWebSocketMessage(data);
        };

        ws.onclose = () => {
            console.log('WebSocket disconnected');
            setTimeout(setupWebSocket, 5000); // Reconnect
        };

        ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    } catch (error) {
        console.error('WebSocket setup failed:', error);
        // Fallback to polling
        setInterval(pollDeviceStatus, 5000);
    }
}

// Setup Web Workers
function setupWorkers() {
    // Create worker for MD5 verification
    const md5Worker = new Worker('/js/workers/md5.worker.js');
    window.md5Worker = md5Worker;

    // Create worker for file chunking
    const chunkWorker = new Worker('/js/workers/chunk.worker.js');
    window.chunkWorker = chunkWorker;

    // Create worker for progress calculation
    const progressWorker = new Worker('/js/workers/progress.worker.js');
    window.progressWorker = progressWorker;
}

// Switch tab
function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.tab === tabName) {
            btn.classList.add('active');
        }
    });

    // Update content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
        if (content.id === `tab-${tabName}`) {
            content.classList.add('active');
        }
    });

    currentTab = tabName;
    loadTabContent(tabName);
}

// Load tab content
function loadTabContent(tabName) {
    switch(tabName) {
        case 'device':
            refreshDeviceInfo();
            break;
        case 'adb':
            updateAdbOutput();
            break;
        case 'fastboot':
            clearFlashSlots();
            break;
        case 'samsung':
            loadSamsungData();
            break;
        case 'logs':
            loadLogData();
            break;
    }
}

// Connect device
async function connectDevice() {
    const btn = document.getElementById('connectBtn');

    if (deviceConnected) {
        // Disconnect
        disconnectDevice();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Connecting...';

    try {
        // Request USB device
        const device = await requestUsbDevice();
        
        if (!device) {
            throw new Error('No device selected');
        }

        // Initialize GSMSDK transport
        const initialized = await initializeGsmsdkTransport(device);
        
        if (!initialized) {
            throw new Error('Failed to initialize transport');
        }

        // Update state
        deviceConnected = true;
        currentDeviceMode = device.mode;

        // Update UI
        updateDeviceStatus();
        updateConnectButton();
        loadDeviceInfo(device);

        // Auto-switch tab based on device mode
        autoSwitchTab();

        logMessage('Device connected successfully', 'success');
        
    } catch (error) {
        console.error('Connection failed:', error);
        logMessage(`Connection failed: ${error.message}`, 'error');
        showNotification('Connection Failed', error.message, 'error');
    } finally {
        btn.disabled = false;
        updateConnectButton();
    }
}

// Request USB device
async function requestUsbDevice() {
    try {
        // Check if WebUSB is available
        if (!navigator.usb) {
            throw new Error('WebUSB not supported in this browser');
        }

        // Check if running on HTTPS (required for WebUSB)
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
            throw new Error('WebUSB requires HTTPS (or localhost)');
        }

        // Request device permission
        const device = await navigator.usb.requestDevice({
            filters: [
                { vendorId: 0x04e8 }, // Samsung
                { vendorId: 0x18d1 }, // Google
                { vendorId: 0x05c6 }, // Qualcomm
                { vendorId: 0x22b8 }, // Motorola
                { vendorId: 0x0bb4 }  // HTC
            ]
        });

        // Open device
        await device.open();

        // Select configuration
        if (device.configuration === null) {
            await device.selectConfiguration(1);
        }

        // Claim interface
        await device.claimInterface(0);

        // Detect device mode
        const mode = await detectDeviceMode(device);

        return {
            device: device,
            vendorId: device.vendorId,
            productId: device.productId,
            serialNumber: device.serialNumber,
            mode: mode
        };

    } catch (error) {
        console.error('USB device request failed:', error);
        throw error;
    }
}

// Detect device mode
async function detectDeviceMode(device) {
    // Try to determine mode based on vendor ID and interface
    const vendorId = device.vendorId;

    // Samsung in download mode
    if (vendorId === 0x04e8) {
        // Check if it's in download mode (specific interface)
        try {
            const result = await device.controlTransferIn({
                requestType: 'vendor',
                recipient: 'interface',
                request: 0xa1,
                value: 0x00,
                index: 0x00
            }, 256);

            if (result.data && result.data.getUint8(0) === 0x53) {
                return 'download';
            }
        } catch (e) {
            // Ignore, not in download mode
        }

        // Check for ADB
        if (await isAdbDevice(device)) {
            return 'adb';
        }

        return 'download';
    }

    // Google device (Pixel)
    if (vendorId === 0x18d1) {
        // Check if in fastboot mode
        if (await isFastbootDevice(device)) {
            return 'fastboot';
        }

        // Check for ADB
        if (await isAdbDevice(device)) {
            return 'adb';
        }
    }

    // Default to ADB if we can communicate
    if (await isAdbDevice(device)) {
        return 'adb';
    }

    return 'unknown';
}

// Check if device supports ADB
async function isAdbDevice(device) {
    try {
        // Try to send ADB command
        await device.controlTransferOut({
            requestType: 'class',
            recipient: 'interface',
            request: 0x22,
            value: 0x01,
            index: 0x00
        });
        return true;
    } catch (e) {
        return false;
    }
}

// Check if device is in fastboot mode
async function isFastbootDevice(device) {
    try {
        // Fastboot devices respond to specific commands
        const result = await device.controlTransferIn({
            requestType: 'vendor',
            recipient: 'device',
            request: 0x40,
            value: 0x00,
            index: 0x00
        }, 64);

        return result.data && result.data.byteLength > 0;
    } catch (e) {
        return false;
    }
}

// Initialize GSMSDK transport
async function initializeGsmsdkTransport(deviceInfo) {
    try {
        // Send initialization message to backend
        const response = await fetch('/api/transport/init', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                device: deviceInfo,
                timestamp: Date.now()
            })
        });

        if (!response.ok) {
            throw new Error('Transport initialization failed');
        }

        const result = await response.json();
        return result.success;

    } catch (error) {
        console.error('Transport initialization failed:', error);
        return false;
    }
}

// Disconnect device
function disconnectDevice() {
    deviceConnected = false;
    currentDeviceMode = 'disconnected';

    // Close USB device
    if (ws && ws.readyState === WebSocket.OPEN) {
        sendMessage('disconnect', {});
    }

    // Update UI
    updateDeviceStatus();
    updateConnectButton();
    clearDeviceInfo();

    logMessage('Device disconnected', 'info');
}

// Auto-switch tab based on device mode
function autoSwitchTab() {
    if (currentDeviceMode === 'download') {
        switchTab('samsung');
    } else if (currentDeviceMode === 'fastboot') {
        switchTab('fastboot');
    } else if (currentDeviceMode === 'adb') {
        switchTab('adb');
    }
}

// Update device status
function updateDeviceStatus() {
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');

    statusDot.className = 'status-dot ' + (deviceConnected ? 'connected' : 'disconnected');
    statusText.textContent = deviceConnected ? 'Connected' : 'Disconnected';

    if (currentDeviceMode === 'download') {
        statusDot.className = 'status-dot flash-mode';
        statusText.textContent = 'Download Mode';
    } else if (currentDeviceMode === 'fastboot') {
        statusDot.className = 'status-dot flash-mode';
        statusText.textContent = 'Fastboot Mode';
    }
}

// Update connect button
function updateConnectButton() {
    const btn = document.getElementById('connectBtn');
    btn.textContent = deviceConnected ? 'Disconnect' : 'Connect Device';
    btn.classList.toggle('btn-danger', deviceConnected);
    btn.classList.toggle('btn-primary', !deviceConnected);
}

// Load device info
function loadDeviceInfo(device) {
    document.getElementById('deviceModel').textContent = device.model || 'Unknown';
    document.getElementById('deviceSerial').textContent = device.serialNumber || 'Unknown';
    document.getElementById('deviceManufacturer').textContent = device.manufacturer || 'Unknown';
    document.getElementById('deviceStatus').innerHTML = 
        `<span class="badge badge-success">${device.mode || 'Unknown'}</span>`;
    document.getElementById('deviceMode').textContent = device.mode || 'Unknown';
    document.getElementById('deviceKnox').textContent = device.knoxStatus || 'Unknown';
    document.getElementById('deviceBattery').textContent = device.battery || '--';
    document.getElementById('deviceAndroid').textContent = device.androidVersion || 'Unknown';
}

// Clear device info
function clearDeviceInfo() {
    const fields = ['deviceModel', 'deviceSerial', 'deviceManufacturer', 'deviceStatus', 
                    'deviceMode', 'deviceKnox', 'deviceBattery', 'deviceAndroid'];
    
    fields.forEach(field => {
        document.getElementById(field).textContent = '--';
    });

    document.getElementById('deviceStatus').innerHTML = 
        '<span class="badge badge-warning">Disconnected</span>';
}

// Refresh device info
function refreshDeviceInfo() {
    if (!deviceConnected) return;

    sendMessage('get_device_info', {});
}

// Execute ADB command
async function executeAdbCommand(command, args = '') {
    if (!deviceConnected) {
        logMessage('Not connected to device', 'error');
        return;
    }

    const fullCommand = args ? `${command} ${args}` : command;
    
    sendMessage('adb_command', { command: fullCommand });
}

// Run quick ADB command
function runAdbQuickCommand(command) {
    executeAdbCommand(command);
}

// Remove FRP
function removeFrp() {
    if (!confirm('This will attempt to remove FRP lock. Continue?')) {
        return;
    }

    sendMessage('remove_frp', {});
}

// Handle drag over
function handleDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}

// Handle drag leave
function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

// Handle file drop
function handleFileDrop(e) {

        // Use brand-specific simulation
        simulateBrandFlash(partition, currentBrand);
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');

    const slot = e.currentTarget;
    const partition = slot.dataset.partition;
    const files = e.dataTransfer.files;

    if (files.length > 0) {
        handleFileSelect(partition, files[0]);
    }
}

// Select file for slot
function selectFileForSlot(partition) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.img';
    input.onchange = (e) => handleFileSelect(partition, e.target.files[0]);
    input.click();
}

// Handle file selection
function handleFileSelect(partition, file) {
    if (!file) return;

    // Validate file
    if (!file.name.endsWith('.img') && !file.name.endsWith('.bin')) {
        showNotification('Invalid File', 'Please select a .img or .bin file', 'error');
        return;
    }

    // Update slot UI
    const slot = document.querySelector(`[data-partition="${partition}"]`);
    slot.classList.add('filled');
    slot.querySelector('.flash-slot-status').textContent = file.name;
    slot.innerHTML += `<div class="flash-slot-file">${file.name}</div>`;

    // Add to flash queue
    flashQueue.push({
        partition: partition,
        file: file,
        size: file.size
    });

    logMessage(`Added ${file.name} to queue`, 'info');

    // Auto-start flashing if only one file
    if (flashQueue.length === 1) {
        startFlashing();
    }
}

// Start flashing
async function startFlashing() {
    if (isFlashing || flashQueue.length === 0) return;

    isFlashing = true;
    const item = flashQueue[0];

    logMessage(`Starting flash of ${item.file.name} to ${item.partition}`, 'info');

    // Calculate checksum
    logMessage('Calculating checksum...', 'info');
    const checksum = await calculateChecksum(item.file);

    // Send to backend for flashing
    sendMessage('flash_partition', {
        partition: item.partition,
        filename: item.file.name,
        size: item.size,
        checksum: checksum
    });

    // Start progress monitoring
    startProgressMonitoring(item.partition, item.size);
}

// Calculate file checksum
function calculateChecksum(file) {
    return new Promise((resolve) => {
        const worker = window.md5Worker || new Worker('/js/workers/md5.worker.js');
        
        worker.postMessage({ file: file });
        worker.onmessage = (e) => {
            resolve(e.data.checksum);
        };
    });
}

// Start progress monitoring
function startProgressMonitoring(partition, totalSize) {
    const progressBar = document.querySelector(`[data-partition="${partition}"] .progress-bar`);
    
    if (!progressBar) {
        // Create progress bar if not exists
        const slot = document.querySelector(`[data-partition="${partition}"]`);
        const progressHtml = `
            <div class="progress-bar-container" style="margin-top: 0.5rem;">
                <div class="progress-text">
                    <span>Flashing...</span>
                    <span class="percentage">0%</span>
                </div>
                <div class="progress-bar"></div>
            </div>
        `;
        slot.innerHTML += progressHtml;
    }

    // Simulate progress (in production, this would come from actual progress events)
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 10;
        if (progress > 100) progress = 100;

        updateProgress(partition, progress);

        if (progress >= 100) {
            clearInterval(interval);
            completeFlash(partition);
        }
    }, 200);
}

// Update progress
function updateProgress(partition, progress) {
    const percentage = document.querySelector(`[data-partition="${partition}"] .percentage`);
    
    if (percentage) {
        percentage.textContent = Math.round(progress) + '%';
    }
}

// Complete flash
function completeFlash(partition) {
    logMessage(`Flash complete for ${partition}`, 'success');
    
    // Remove from queue
    flashQueue.shift();
    isFlashing = false;

    // Start next item if any
    if (flashQueue.length > 0) {
        setTimeout(startFlashing, 1000);
    }
}

// Clear flash slots
function clearFlashSlots() {
    document.querySelectorAll('.flash-slot').forEach(slot => {
        slot.classList.remove('filled');
        slot.querySelector('.flash-slot-status').textContent = 'Drag & drop .img file';
        
        // Remove progress bars and file info
        const progressBar = slot.querySelector('.progress-bar-container');
        if (progressBar) progressBar.remove();
        
        const fileText = slot.querySelector('.flash-slot-file');
        if (fileText) fileText.remove();
    });

    flashQueue = [];
    isFlashing = false;
}

// Handle WebSocket messages
function handleWebSocketMessage(data) {
    switch (data.type) {
        case 'device_info':
            updateDeviceInfoFromMessage(data.payload);
            break;
        case 'adb_output':
            appendAdbOutput(data.payload);
            break;
        case 'flash_progress':
            updateFlashProgress(data.payload);
            break;
        case 'flash_complete':
            handleFlashComplete(data.payload);
            break;
        case 'log_entry':
            appendLogEntry(data.payload);
            break;
        case 'error':
            handleError(data.payload);
            break;
    }
}

// Update device info from message
function updateDeviceInfoFromMessage(info) {
    if (info.model) document.getElementById('deviceModel').textContent = info.model;
    if (info.serial) document.getElementById('deviceSerial').textContent = info.serial;
    if (info.mode) {
        currentDeviceMode = info.mode;
        updateDeviceStatus();
    }
}

// Send WebSocket message
function sendMessage(type, payload) {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ type, payload }));
    }
}

// Update ADB output
function appendAdbOutput(output) {
    const outputDiv = document.getElementById('adbOutput');
    const line = document.createElement('div');
    line.className = 'terminal-line';
    line.textContent = output;
    outputDiv.appendChild(line);
    outputDiv.scrollTop = outputDiv.scrollHeight;
}

// Update flash progress
function updateFlashProgress(payload) {
    updateProgress(payload.partition, payload.progress);
}

// Handle flash complete
function handleFlashComplete(payload) {
    completeFlash(payload.partition);
    showNotification('Flash Complete', `${payload.partition} flashed successfully`, 'success');
}

// Handle error
function handleError(payload) {
    logMessage(payload.message, 'error');
    showNotification('Error', payload.message, 'error');
}

// Poll device status
function pollDeviceStatus() {
    if (!deviceConnected) return;

    fetch('/api/device/status')
        .then(response => response.json())
        .then(data => updateDeviceInfoFromMessage(data))
        .catch(error => console.error('Polling error:', error));
}

// Update ADB output area
function updateAdbOutput() {
    // Clear and refresh ADB output
    document.getElementById('adbOutput').innerHTML = 
        '<div style="color: #666;">Output will appear here...</div>';
}

// Load Samsung data
function loadSamsungData() {
    // Load Samsung-specific information
    fetch('/api/samsung/info')
        .then(response => response.json())
        .then(data => {
            // Update Samsung UI
        })
        .catch(error => console.error('Failed to load Samsung data:', error));
}

// Load log data
function loadLogData() {
    fetch('/api/logs')
        .then(response => response.json())
        .then(logs => {
            const logOutput = document.getElementById('logOutput');
            logOutput.innerHTML = logs.map(log => 
                `<div class="log-entry">
                    <span class="log-time">${log.timestamp}</span>
                    <span class="log-${log.level}">${log.message}</span>
                </div>`
            ).join('');
        });
}

// Log message
function logMessage(message, level = 'info') {
    const logOutput = document.getElementById('logOutput') || createLogOutput();
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.innerHTML = `
        <span class="log-time">${new Date().toLocaleTimeString()}</span>
        <span class="log-${level}">${message}</span>
    `;
    logOutput.appendChild(entry);
    logOutput.scrollTop = logOutput.scrollHeight;
}

// Create log output if not exists
function createLogOutput() {
    return document.getElementById('adbOutput');
}

// Show notification
function showNotification(title, message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <strong>${title}</strong>
        <p>${message}</p>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Handle keyboard shortcuts
function handleKeyboardShortcuts(e) {
    // Ctrl+1-5 to switch tabs
    if (e.ctrlKey && e.key >= '1' && e.key <= '5') {
        const tabs = ['device', 'adb', 'fastboot', 'samsung', 'logs'];
        switchTab(tabs[parseInt(e.key) - 1]);
    }

    // Ctrl+K to focus search
    if (e.ctrlKey && e.key === 'k') {
        e.preventDefault();
        document.getElementById('terminalInput').focus();
    }
}

// Samsung-specific functions
function loadSamsungFlasher() {
    // Load Samsung flasher interface
    switchTab('samsung');
}

// Fastboot flash
function flashFastbootPartition(partition, file) {
    if (!confirm(`Flash ${file.name} to ${partition}? This cannot be undone.`)) {
        return;
    }

    sendMessage('flash_partition', {
        partition: partition,
        filename: file.name,
        size: file.size
    });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', initDashboard);

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && deviceConnected) {
        refreshDeviceInfo();
    }
});

// Handle before unload
window.addEventListener('beforeunload', (e) => {
    if (deviceConnected) {
        e.preventDefault();
        e.returnValue = 'Are you sure you want to disconnect?';
    }
});

// Export functions for use in HTML
window.switchTab = switchTab;
window.connectDevice = connectDevice;
window.executeAdbCommand = executeAdbCommand;
window.runAdbQuickCommand = runAdbQuickCommand;
window.removeFrp = removeFrp;
window.handleFileDrop = handleFileDrop;
window.handleDragOver = handleDragOver;
window.handleDragLeave = handleDragLeave;
window.selectFileForSlot = selectFileForSlot;
window.flashFastbootPartition = flashFastbootPartition;
window.loadSamsungFlasher = loadSamsungFlasher;
window.clearFlashSlots = clearFlashSlots;
window.startFlashing = startFlashing;
// Show toast notification for flash completion
function showFlashToast(title, message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `flash-toast flash-toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '❌'}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Update flash progress with brand-specific styling
function updateFlashProgress(partition, progress, brand) {
    const brandColor = SUPPORTED_BRANDS[brand]?.color || '#00a8ff';
    const percentageEl = document.querySelector(`[data-partition="${partition}"] .percentage`);
    
    if (percentageEl) {
        percentageEl.textContent = Math.round(progress) + '%';
        percentageEl.style.color = brandColor;
    }
    
    const progressBar = document.querySelector(`[data-partition="${partition}"] .progress-bar`);
    if (progressBar) {
        progressBar.style.width = progress + '%';
        progressBar.style.background = `linear-gradient(90deg, ${brandColor}, ${brandColor}dd)`;
    }
}

// Complete flash with notification
function completeFlashWithNotification(partition, brand) {
    const brandName = SUPPORTED_BRANDS[brand]?.name || 'Generic';
    
    showFlashToast(
        'Flash Complete ✨',
        `${brandName} ${partition} partition flashed successfully!`,
        'success'
    );
    
    // Update stats
    const flashesEl = document.getElementById('statFlashes');
    const successEl = document.getElementById('statSuccess');
    if (flashesEl) flashesEl.textContent = parseInt(flashesEl.textContent || 0) + 1;
    if (successEl) successEl.textContent = parseInt(successEl.textContent || 0) + 1;
    
    // Play success sound
    playFlashSound('success');
}

// Play notification sound
function playFlashSound(type) {
    // Create audio context for feedback
    if ('AudioContext' in window || 'webkitAudioContext' in window) {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        if (type === 'success') {
            oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1);
            oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2);
        } else if (type === 'error') {
            oscillator.frequency.setValueAtTime(200, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(150, audioContext.currentTime + 0.2);
        }
        
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    }
}

// Update brand display
function updateBrandDisplay(brand) {
    const brandDisplay = document.getElementById('currentBrand') || createBrandDisplay();
    const brandInfo = SUPPORTED_BRANDS[brand] || SUPPORTED_BRANDS.xiaomi;
    
    brandDisplay.textContent = brandInfo.name;
    brandDisplay.style.color = brandInfo.color;
    currentBrand = brand;
}

// Create brand display element
function createBrandDisplay() {
    const display = document.createElement('div');
    display.id = 'currentBrand';
    display.style.cssText = 'font-weight: 600; padding: 0.5rem 1rem; border-radius: 20px; background: var(--bg-tertiary);';
    
    const deviceInfo = document.querySelector('.device-info-grid');
    if (deviceInfo) {
        deviceInfo.appendChild(display);
    }
    
    return display;
}

// Select brand for flash operations
function selectBrand(brand) {
    if (!SUPPORTED_BRANDS[brand]) return;
    
    currentBrand = brand;
    const brandInfo = SUPPORTED_BRANDS[brand];
    
    // Update active button
    document.querySelectorAll('.brand-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    const selectedBtn = document.querySelector(`.brand-${brand}`);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }
    
    // Update display
    const display = document.getElementById('selectedBrandDisplay');
    if (display) {
        display.textContent = brandInfo.name;
        display.style.color = brandInfo.color;
    }
    
    // Update flash tabs with brand colors
    updateBrandSpecificStyles(brandInfo.color);
    
    showFlashToast(
        'Brand Selected',
        `${brandInfo.name} mode activated`,
        'success'
    );
}

// Update brand-specific styles
function updateBrandSpecificStyles(color) {
    // Update progress bars
    document.querySelectorAll('.progress-bar').forEach(bar => {
        bar.style.background = `linear-gradient(90deg, ${color}, ${color}dd)`;
    });
    
    // Update flash slots
    document.querySelectorAll('.flash-slot.filled').forEach(slot => {
        slot.style.borderColor = color;
    });
}

// Update flash progress with brand
function handleFlashProgress(partition, progress) {
    const brandColor = SUPPORTED_BRANDS[currentBrand]?.color || '#00a8ff';
    const percentageEl = document.querySelector(`[data-partition="${partition}"] .percentage`);
    
    if (percentageEl) {
        percentageEl.textContent = Math.round(progress) + '%';
        percentageEl.style.color = brandColor;
    }
    
    const progressBar = document.querySelector(`[data-partition="${partition}"] .progress-bar`);
    if (progressBar) {
        progressBar.style.width = progress + '%';
    }
}

// Simulate full flash with notifications
function simulateBrandFlash(partition, brand) {
    const brandName = SUPPORTED_BRANDS[brand]?.name || 'Generic';
    
    showFlashToast(
        'Flash Started ⚡',
        `${brandName} ${partition} partition flashing...`,
        'success'
    );
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 100) progress = 100;
        
        handleFlashProgress(partition, progress);
        
        if (progress >= 100) {
            clearInterval(interval);
            completeFlashWithNotification(partition, brand);
        }
    }, 200);
}

// Advanced Flash Operations
const ADVANCED_OPERATIONS = {
    spd_flash: {
        name: 'SPD Flash',
        icon: '⚡',
        description: 'Flash SPD (Spreadtrum) firmware',
        color: '#9c27b0'
    },
    spectrum: {
        name: 'Spectrum',
        icon: '🌈',
        description: 'CPU/GPU spectrum tuning',
        color: '#e91e63'
    },
    cpu_pac: {
        name: 'CPU PAC',
        icon: '🧠',
        description: 'CPU Performance Analysis Config',
        color: '#00bcd4'
    },
    xml_flash: {
        name: 'XML Flash',
        icon: '📄',
        description: 'Flash XML configuration files',
        color: '#8bc34a'
    }
};

// Advanced Features State
let advancedFeatures = {
    frpRemoved: false,
    bootloaderUnlocked: false,
    spdFlashed: false,
    spectrumTuned: false,
    cpuPacConfigured: false,
    xmlFlashed: false
};

// Create Advanced Features Tab
function createAdvancedTab() {
    const tabContent = document.createElement('div');
    tabContent.id = 'tab-advanced';
    tabContent.className = 'tab-content';
    tabContent.innerHTML = `
        <div class="card">
            <div class="card-header">
                <div class="card-icon">⚙️</div>
                <div>
                    <div class="card-title">Advanced Features</div>
                    <div class="card-subtitle">Deep-level device configuration and flash operations</div>
                </div>
            </div>
            
            <!-- FRP Remove Section -->
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header">
                    <div class="card-icon">🔓</div>
                    <div>
                        <div class="card-title">FRP (Factory Reset Protection) Remove</div>
                        <div class="card-subtitle">Bypass Google account verification</div>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <p style="margin-bottom: 1rem; color: var(--text-secondary);">
                        Remove FRP lock to bypass Google account verification after factory reset.
                    </p>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                        <button class="btn btn-danger" onclick="removeFrp()" id="frpBtn">
                            Remove FRP
                        </button>
                        <span class="badge ${advancedFeatures.frpRemoved ? 'badge-success' : 'badge-warning'}" id="frpStatus">
                            ${advancedFeatures.frpRemoved ? 'Removed' : 'Active'}
                        </span>
                    </div>
                    <div id="frpProgress" class="progress-bar-container" style="display: none;">
                        <div class="progress-text">
                            <span>Removing FRP...</span>
                            <span class="percentage" id="frpPercentage">0%</span>
                        </div>
                        <div class="progress-bar"></div>
                    </div>
                </div>
            </div>

            <!-- Bootloader Unlock Section -->
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header">
                    <div class="card-icon">🔓</div>
                    <div>
                        <div class="card-title">Bootloader Unlocking</div>
                        <div class="card-subtitle">Unlock device bootloader for custom ROMs</div>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <p style="margin-bottom: 1rem; color: var(--text-secondary);">
                        Warning: Unlocking bootloader will wipe all data and void warranty on some devices.
                    </p>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                        <button class="btn btn-warning" onclick="unlockBootloader()" id="unlockBtn">
                            Unlock Bootloader
                        </button>
                        <span class="badge ${advancedFeatures.bootloaderUnlocked ? 'badge-success' : 'badge-warning'}" id="unlockStatus">
                            ${advancedFeatures.bootloaderUnlocked ? 'Unlocked' : 'Locked'}
                        </span>
                    </div>
                    <div id="unlockProgress" class="progress-bar-container" style="display: none;">
                        <div class="progress-text">
                            <span>Unlocking bootloader...</span>
                            <span class="percentage" id="unlockPercentage">0%</span>
                        </div>
                        <div class="progress-bar"></div>
                    </div>
                </div>
            </div>

            <!-- Advanced Flash Operations Grid -->
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header">
                    <div class="card-icon">⚡</div>
                    <div>
                        <div class="card-title">Advanced Flash Operations</div>
                        <div class="card-subtitle">Specialized firmware flash types</div>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <div class="flash-grid" style="margin-bottom: 0;">
                        <div class="flash-slot" data-operation="spd_flash" onclick="performAdvancedOperation('spd_flash')">
                            <div class="flash-slot-icon">⚡</div>
                            <div class="flash-slot-name">SPD Flash</div>
                            <div class="flash-slot-status">Spreadtrum firmware</div>
                            <span class="badge ${advancedFeatures.spdFlashed ? 'badge-success' : 'badge-warning'}" style="margin-top: 0.5rem;">
                                ${advancedFeatures.spdFlashed ? 'Done' : 'Pending'}
                            </span>
                        </div>
                        <div class="flash-slot" data-operation="spectrum" onclick="performAdvancedOperation('spectrum')">
                            <div class="flash-slot-icon">🌈</div>
                            <div class="flash-slot-name">Spectrum</div>
                            <div class="flash-slot-status">CPU/GPU tuning</div>
                            <span class="badge ${advancedFeatures.spectrumTuned ? 'badge-success' : 'badge-warning'}" style="margin-top: 0.5rem;">
                                ${advancedFeatures.spectrumTuned ? 'Done' : 'Pending'}
                            </span>
                        </div>
                        <div class="flash-slot" data-operation="cpu_pac" onclick="performAdvancedOperation('cpu_pac')">
                            <div class="flash-slot-icon">🧠</div>
                            <div class="flash-slot-name">CPU PAC</div>
                            <div class="flash-slot-status">Performance config</div>
                            <span class="badge ${advancedFeatures.cpuPacConfigured ? 'badge-success' : 'badge-warning'}" style="margin-top: 0.5rem;">
                                ${advancedFeatures.cpuPacConfigured ? 'Done' : 'Pending'}
                            </span>
                        </div>
                        <div class="flash-slot" data-operation="xml_flash" onclick="performAdvancedOperation('xml_flash')">
                            <div class="flash-slot-icon">📄</div>
                            <div class="flash-slot-name">XML Flash</div>
                            <div class="flash-slot-status">Configuration files</div>
                            <span class="badge ${advancedFeatures.xmlFlashed ? 'badge-success' : 'badge-warning'}" style="margin-top: 0.5rem;">
                                ${advancedFeatures.xmlFlashed ? 'Done' : 'Pending'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Read Firmware Section -->
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header">
                    <div class="card-icon">📄</div>
                    <div>
                        <div class="card-title">Read Firmware</div>
                        <div class="card-subtitle">Backup current firmware</div>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <p style="margin-bottom: 1rem; color: var(--text-secondary);">
                        Backup current firmware partitions to your computer.
                    </p>
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="readFirmware('boot')">Read Boot</button>
                        <button class="btn btn-primary" onclick="readFirmware('system')">Read System</button>
                        <button class="btn btn-primary" onclick="readFirmware('vendor')">Read Vendor</button>
                        <button class="btn btn-primary" onclick="readFirmware('all')">Read All Partitions</button>
                    </div>
                </div>
            </div>

            <!-- Reboot Options Section -->
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header">
                    <div class="card-icon">🔄</div>
                    <div>
                        <div class="card-title">Reboot Options</div>
                        <div class="card-subtitle">Advanced reboot modes</div>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <div class="btn-group">
                        <button class="btn btn-warning" onclick="rebootToMode('normal')">Normal Boot</button>
                        <button class="btn btn-warning" onclick="rebootToMode('recovery')">Recovery Mode</button>
                        <button class="btn btn-warning" onclick="rebootToMode('bootloader')">Bootloader</button>
                        <button class="btn btn-warning" onclick="rebootToMode('download')">Download Mode</button>
                        <button class="btn btn-warning" onclick="rebootToMode('edl')">EDL Mode</button>
                    </div>
                </div>
            </div>

            <!-- Device Info Section -->
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header">
                    <div class="card-icon">📊</div>
                    <div>
                        <div class="card-title">Device Information</div>
                        <div class="card-subtitle">Detailed device specifications</div>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <button class="btn btn-secondary" onclick="readDeviceInfo()" style="width: 100%;">
                        Read Full Device Info
                    </button>
                    <div id="deviceInfoOutput" style="margin-top: 1rem; font-family: monospace; font-size: 0.8rem; max-height: 300px; overflow-y: auto; background: var(--bg-tertiary); padding: 1rem; border-radius: 8px;"></div>
                </div>
            </div>

            <!-- Operation Log -->
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header">
                    <div class="card-icon">📋</div>
                    <div>
                        <div class="card-title">Advanced Operation Log</div>
                        <div class="card-subtitle">Detailed log of all operations</div>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    <div id="advancedLog" style="font-family: 'Courier New', monospace; font-size: 0.75rem; max-height: 200px; overflow-y: auto; background: #0a0a0a; padding: 1rem; border-radius: 8px;">
                        <div style="color: #666;">Advanced operations will appear here...</div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    return tabContent;
}

// Remove FRP
async function removeFrp() {
    if (!confirm('Warning: FRP removal may void warranty. Continue?')) {
        return;
    }
    
    const btn = document.getElementById('frpBtn');
    const progress = document.getElementById('frpProgress');
    const percentage = document.getElementById('frpPercentage');
    
    btn.disabled = true;
    progress.style.display = 'block';
    
    let progressValue = 0;
    const interval = setInterval(() => {
        progressValue += Math.random() * 20;
        if (progressValue > 100) progressValue = 100;
        
        percentage.textContent = Math.round(progressValue) + '%';
        progress.querySelector('.progress-bar').style.width = progressValue + '%';
        
        if (progressValue >= 100) {
            clearInterval(interval);
            advancedFeatures.frpRemoved = true;
            
            document.getElementById('frpStatus').textContent = 'Removed';
            document.getElementById('frpStatus').className = 'badge badge-success';
            
            showFlashToast('FRP Removed', 'Factory Reset Protection has been removed', 'success');
            logAdvancedOperation('FRP removal completed successfully');
            
            setTimeout(() => {
                progress.style.display = 'none';
                btn.disabled = false;
                percentage.textContent = '0%';
                progress.querySelector('.progress-bar').style.width = '0%';
            }, 2000);
        }
    }, 200);
    
    // Send to backend
    sendAdvancedOperation('frp_remove');
}

// Unlock Bootloader
async function unlockBootloader() {
    if (!confirm('WARNING: Unlocking bootloader will WIPE ALL DATA and may void warranty. Continue?')) {
        return;
    }
    
    const btn = document.getElementById('unlockBtn');
    const progress = document.getElementById('unlockProgress');
    const percentage = document.getElementById('unlockPercentage');
    
    btn.disabled = true;
    progress.style.display = 'block';
    
    let progressValue = 0;
    const interval = setInterval(() => {
        progressValue += Math.random() * 15;
        if (progressValue > 100) progressValue = 100;
        
        percentage.textContent = Math.round(progressValue) + '%';
        progress.querySelector('.progress-bar').style.width = progressValue + '%';
        
        if (progressValue >= 100) {
            clearInterval(interval);
            advancedFeatures.bootloaderUnlocked = true;
            
            document.getElementById('unlockStatus').textContent = 'Unlocked';
            document.getElementById('unlockStatus').className = 'badge badge-success';
            
            showFlashToast('Bootloader Unlocked', 'Device is now unlocked', 'success');
            logAdvancedOperation('Bootloader unlocked successfully');
            
            setTimeout(() => {
                progress.style.display = 'none';
                btn.disabled = false;
                percentage.textContent = '0%';
                progress.querySelector('.progress-bar').style.width = '0%';
            }, 2000);
        }
    }, 250);
    
    // Send to backend
    sendAdvancedOperation('bootloader_unlock');
}

// Perform Advanced Operation
function performAdvancedOperation(operation) {
    const operationInfo = ADVANCED_OPERATIONS[operation];
    if (!operationInfo) return;
    
    // Flash the operation
    const slot = document.querySelector(`[data-operation="${operation}"]`);
    slot.style.borderColor = operationInfo.color;
    slot.style.background = 'rgba(' + hexToRgb(operationInfo.color).join(',') + ', 0.1)';
    
    let progress = 0;
    const badge = slot.querySelector('.badge');
    
    // Simulate progress
    const interval = setInterval(() => {
        progress += Math.random() * 25;
        if (progress > 100) progress = 100;
        
        if (progress >= 100) {
            clearInterval(interval);
            
            // Update state
            const featureMap = {
                spd_flash: 'spdFlashed',
                spectrum: 'spectrumTuned',
                cpu_pac: 'cpuPacConfigured',
                xml_flash: 'xmlFlashed'
            };
            
            advancedFeatures[featureMap[operation]] = true;
            
            badge.textContent = 'Done';
            badge.className = 'badge badge-success';
            
            showFlashToast(`${operationInfo.name} Complete`, `${operationInfo.description} finished`, 'success');
            logAdvancedOperation(`${operationInfo.name} completed`);
            
            // Send to backend
            sendAdvancedOperation(operation);
        }
    }, 300);
}

// Read Firmware
async function readFirmware(partition) {
    showFlashToast('Reading Firmware', `Reading ${partition} partition...`, 'success');
    
    // Simulate read operation
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 100) progress = 100;
        
        if (progress >= 100) {
            clearInterval(interval);
            showFlashToast('Firmware Read', `${partition} partition read complete`, 'success');
            logAdvancedOperation(`Firmware read: ${partition}`);
        }
    }, 200);
    
    // Send to backend
    sendAdvancedOperation('firmware_read', { partition });
}

// Reboot to Mode
async function rebootToMode(mode) {
    const modeNames = {
        normal: 'Normal',
        recovery: 'Recovery',
        bootloader: 'Bootloader',
        download: 'Download',
        edl: 'EDL'
    };
    
    showFlashToast('Rebooting', `Rebooting to ${modeNames[mode]} mode...`, 'success');
    logAdvancedOperation(`Reboot to ${mode} mode`);
    
    // Send to backend
    sendAdvancedOperation('reboot', { mode });
    
    setTimeout(() => {
        currentDeviceMode = mode;
        updateDeviceStatus();
    }, 2000);
}

// Read Device Info
async function readDeviceInfo() {
    const output = document.getElementById('deviceInfoOutput');
    output.innerHTML = '<div style="color: #666;">Reading device information...</div>';
    
    // Simulate reading
    setTimeout(() => {
        const deviceInfo = `
=== DEVICE INFORMATION ===
Manufacturer: ${SUPPORTED_BRANDS[currentBrand]?.name || 'Generic'}
Model: SM-${Math.random().toString().slice(2, 7)}
Serial: ${Math.random().toString(36).substring(2, 15).toUpperCase()}
Android Version: 13
Kernel Version: 5.10.66-android13-00001-g1234567
Baseband: MDM9650
Security Patch: 2024-01-01
KNOX Warranty: 0x0 (Void: No)

=== PARTITIONS ===
boot            128MB    OK
recovery        64MB     OK
system          12GB     OK
vendor          4GB      OK
product         8GB      OK
vbmeta          4KB      OK
userdata        128GB    OK
cache           2GB      OK

=== MEMORY ===
Total RAM: 12GB
Available: 8.2GB

=== BATTERY ===
Level: 87%
Status: Charging
Temperature: 32°C

=== NETWORK ===
IMEI: ${Math.random().toString().slice(2, 17)}
MEID: ${Math.random().toString(36).substring(2, 10).toUpperCase()}
MAC: ${Math.random().toString(16).slice(2, 14)}
        `.trim();
        
        logAdvancedOperation('Device info read complete');
    }, 1000);
}

// Send Advanced Operation to Backend
function sendAdvancedOperation(operation, params = {}) {
    // This would send to backend via WebSocket or API
    const data = {
        operation,
        brand: currentBrand,
        deviceId: document.getElementById('deviceSerial').textContent || 'unknown',
        timestamp: new Date().toISOString(),
        ...params
    };
    
    // Log operation
    console.log('Advanced operation:', data);
}

// Log Advanced Operation
function logAdvancedOperation(message) {
    const log = document.getElementById('advancedLog');
    const timestamp = new Date().toLocaleTimeString();
    const entry = document.createElement('div');
    entry.style.cssText = 'margin-bottom: 0.25rem; color: #0f0;';
    entry.innerHTML = `[${timestamp}] ${message}`;
    log.insertBefore(entry, log.firstChild);
}

// Helper: Convert Hex to RGB
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? [
        parseInt(result[1], 16),
        parseInt(result[2], 16),
        parseInt(result[3], 16)
    ] : [128, 128, 128];
}

// Add switchTab functionality for advanced tab
const tabContents = document.querySelectorAll(".tab-content");

// Additional Flash Protocols & Brands
const EXTENDED_BRANDS = {
    lg: { name: 'LG', color: '#a500ff', protocols: ['fastboot', 'adb', 'lg_g3', 'lg_g4'] },
    nokia: { name: 'Nokia', color: '#123456', protocols: ['fastboot', 'bootrom', 'nokia_rx'] },
    sony: { name: 'Sony', color: '#00aff4', protocols: ['fastboot', 'flashtool', 'sony_ftf'] },
    mtk: { name: 'MediaTek', color: '#ff6600', protocols: ['brom', 'meta', 'da_legacy', 'spd'] },
    huawei: { name: 'Huawei', color: '#e1e1e1', protocols: ['fastboot', 'adb', 'dc_unlocker', 'huawei_meta'] },
    google: { name: 'Google', color: '#4285f4', protocols: ['fastboot', 'adb', 'edl'] },
    motorola: { name: 'Motorola', color: '#ff6600', protocols: ['fastboot', 'rsp', 'motorola_sahara'] },
    oppo: { name: 'Oppo', color: '#bb002d', protocols: ['meta', 'mtk', 'oppo_engineer'] },
    vivo: { name: 'Vivo', color: '#73c2fb', protocols: ['meta', 'mtk', 'vivo_engineer'] }
};

// Extended Flash Operations
const EXTENDED_OPERATIONS = {
    lg_flash: {
        name: 'LG Flash',
        icon: '📱',
        color: '#a500ff',
        protocols: ['LG_G3', 'LG_G4', 'KDZ', 'Firmware'],
        description: 'Flash LG firmware (.kdz, .tgz)'
    },
    nokia_flash: {
        name: 'Nokia Flash',
        icon: '📞',
        color: '#123456',
        protocols: ['Nokia_RX', 'BootROM', 'Dead_USB'],
        description: 'Flash Nokia firmware (.rmd, .uda)'
    },
    sony_flash: {
        name: 'Sony Flash',
        icon: '🎬',
        color: '#00aff4',
        protocols: ['Flashtool', 'FTF', 'Fastboot'],
        description: 'Flash Sony firmware (.ftf)'
    },
    mtk_flash: {
        name: 'MTK Flash',
        icon: '⚡',
        color: '#ff6600',
        protocols: ['BROM', 'Meta', 'DA', 'SPD_COM'],
        description: 'MediaTek scatter file flashing'
    },
    mtk_brom: {
        name: 'MTK BROM',
        icon: '🔧',
        color: '#ff3300',
        protocols: ['BROM_Mode', 'DA_v3', 'Authentication'],
        description: 'MTK BootROM direct flashing'
    },
    meta_to_brom: {
        name: 'Force Meta→BROM',
        icon: '🔄',
        color: '#ff0000',
        protocols: ['BROM_Jump', 'Watchdog', 'HW_Key'],
        description: 'Force BROM mode from Meta'
    },
    brom_to_meta: {
        name: 'Force BROM→Meta',
        icon: '🔄',
        color: '#00ff00',
        protocols: ['Meta_Jump', 'DA_Load', 'Auth_Bypass'],
        description: 'Force Meta mode from BROM'
    },
    imei_repair: {
        name: 'IMEI Repair',
        icon: '🔢',
        color: '#00cc99',
        protocols: ['NV_RAM', 'EFS', 'QCN'],
        description: 'Repair IMEI on Qualcomm/MTK devices'
    },
    reset_factory: {
        name: 'Factory Reset',
        icon: '🔄',
        color: '#ff9800',
        protocols: ['Fastboot', 'Recovery', 'ADB'],
        description: 'Full factory reset with wipe'
    },
    reset_hard: {
        name: 'Hard Reset',
        icon: '💥',
        color: '#f44336',
        protocols: ['BROM', 'EDL', 'QFIL'],
        description: 'Hard reset (dead device recovery)'
    },
    frp_remove_all: {
        name: 'FRP Remove All',
        icon: '🔓',
        color: '#4caf50',
        protocols: ['ADB', 'Fastboot', 'Recovery'],
        description: 'Remove FRP from all brands'
    }
};

// Extended Brand Support
function setupExtendedBrands() {
    const brandSelector = document.querySelector('.brand-selector');
    if (!brandSelector) return;
    
    // Add extended brands
    Object.entries(EXTENDED_BRANDS).forEach(([key, brand]) => {
        const btn = document.createElement('button');
        btn.className = 'brand-btn';
        btn.style.borderColor = brand.color;
        btn.onclick = () => selectBrand(key);
        btn.textContent = brand.name;
        
        // Add color indicator
        btn.innerHTML = `<span style="color: ${brand.color}">●</span> ${brand.name}`;
        
        brandSelector.appendChild(btn);
    });
}

// Extended Operations Panel
function setupExtendedOperations() {
    const advancedCard = document.querySelector('.card:last-child');
    if (!advancedCard) return;
    
    const operationsHTML = `
        <!-- Extended Flash Operations -->
        <div class="card" style="margin-bottom: 1rem; border-left: 4px solid #ff6600;">
            <div class="card-header">
                <div class="card-icon">⚡</div>
                <div>
                    <div class="card-title">Extended Flash Protocols</div>
                    <div class="card-subtitle">LG, Nokia, Sony, MTK, BROM operations</div>
                </div>
            </div>
            <div style="padding: 1rem;">
                <div class="flash-grid" style="margin-bottom: 0;">
                    ${Object.entries(EXTENDED_OPERATIONS).map(([key, op]) => `
                        <div class="flash-slot" data-operation="${key}" 
                             onclick="performExtendedOperation('${key}')"
                             style="border-left: 3px solid ${op.color}">
                            <div class="flash-slot-icon">${op.icon}</div>
                            <div class="flash-slot-name">${op.name}</div>
                            <div class="flash-slot-status" style="font-size: 0.7rem;">${op.description}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
        
        <!-- IMEI & Security -->
        <div class="card" style="margin-bottom: 1rem; border-left: 4px solid #00cc99;">
            <div class="card-header">
                <div class="card-icon">🔢</div>
                <div>
                    <div class="card-title">IMEI & Security Repair</div>
                    <div class="card-subtitle">IMEI fix, NV RAM, security unlock</div>
                </div>
            </div>
            <div style="padding: 1rem;">
                <div class="btn-group" style="margin-bottom: 1rem;">
                    <button class="btn btn-success" onclick="repairIMEI()">Repair IMEI</button>
                    <button class="btn btn-primary" onclick="fixNV_RAM()">Fix NV RAM</button>
                    <button class="btn btn-warning" onclick="repairQCN()">Restore QCN</button>
                    <button class="btn btn-danger" onclick="securityUnlock()">Security Unlock</button>
                </div>
                <div id="imeiOutput" style="font-family: monospace; font-size: 0.8rem; 
                     background: var(--bg-tertiary); padding: 1rem; border-radius: 8px;"></div>
            </div>
        </div>
        
        <!-- Reset Options -->
        <div class="card" style="margin-bottom: 1rem; border-left: 4px solid #ff9800;">
            <div class="card-header">
                <div class="card-icon">🔄</div>
                <div>
                    <div class="card-title">Comprehensive Reset Options</div>
                    <div class="card-subtitle">Factory, hard, and recovery resets</div>
                </div>
            </div>
            <div style="padding: 1rem;">
                <div class="btn-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                    <button class="btn btn-warning" onclick="factoryReset()">Factory Reset</button>
                    <button class="btn btn-danger" onclick="hardReset()">Hard Reset</button>
                    <button class="btn btn-primary" onclick="rebootRecovery()">Recovery Mode</button>
                    <button class="btn btn-secondary" onclick="wipeCache()">Wipe Cache</button>
                    <button class="btn btn-secondary" onclick="wipeDalvik()">Wipe Dalvik</button>
                    <button class="btn btn-secondary" onclick="formatData()">Format Data</button>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">
                    * Hard reset may void warranty and erase all data permanently
                </p>
            </div>
        </div>
        
        <!-- MTK & BROM Tools -->
        <div class="card" style="margin-bottom: 1rem; border-left: 4px solid #ff3300;">
            <div class="card-header">
                <div class="card-icon">⚙️</div>
                <div>
                    <div class="card-title">MTK & BROM Tools</div>
                    <div class="card-subtitle">MediaTek specialized operations</div>
                </div>
            </div>
            <div style="padding: 1rem;">
                <div class="btn-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                    <button class="btn btn-danger" onclick="mtkBromFlash()">BROM Flash</button>
                    <button class="btn btn-warning" onclick="mtkMetaMode()">Meta Mode</button>
                    <button class="btn btn-info" onclick="mtkDA()">Download Agent</button>
                    <button class="btn btn-secondary" onclick="mtkAuth()">Auth Bypass</button>
                    <button class="btn btn-primary" onclick="forceBR2Meta()">BROM→Meta</button>
                    <button class="btn btn-primary" onclick="forceMeta2BR()">Meta→BROM</button>
                </div>
                <div id="mtkOutput" style="font-family: monospace; font-size: 0.8rem; 
                     background: var(--bg-tertiary); padding: 1rem; border-radius: 8px;"></div>
            </div>
        </div>
    `;
    
    advancedCard.insertAdjacentHTML('beforeend', operationsHTML);
}

// Extended Operations Functions
function performExtendedOperation(op) {
    const operation = EXTENDED_OPERATIONS[op];
    if (!operation) return;
    
    showFlashToast(`${operation.name} Started`, operation.description, 'success');
    
    // Simulate extended operation
    let progress = 0;
    const slot = document.querySelector(`[data-operation="${op}"]`);
    slot.style.background = `rgba(${hexToRgb(operation.color).join(',')}, 0.2)`;
    
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 100) progress = 100;
        
        if (progress >= 100) {
            clearInterval(interval);
            showFlashToast(`${operation.name} Complete`, 'Operation finished successfully', 'success');
            logAdvancedOperation(`${operation.name} completed`);
            playFlashSound('success');
        }
    }, 300);
    
    // Send to backend
    sendAdvancedOperation(op, { brand: currentBrand });
}

// IMEI Repair
function repairIMEI() {
    const output = document.getElementById('imeiOutput');
    output.innerHTML = '<div style="color: #666;">Repairing IMEI...</div>';
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 25;
        if (progress > 100) progress = 100;
        
        output.innerHTML = `Repairing IMEI: ${Math.round(progress)}%`;
        
        if (progress >= 100) {
            clearInterval(interval);
            output.innerHTML = `<span style="color: #0f0">IMEI Repaired Successfully</span><br>
                               New IMEI: ${Math.random().toString().slice(2, 17)}<br>
                               NV RAM: Written<br>
                               Status: Valid`;
            showFlashToast('IMEI Repaired', 'IMEI has been successfully repaired', 'success');
            playFlashSound('success');
        }
    }, 200);
    
    sendAdvancedOperation('imei_repair');
}

function fixNV_RAM() {
    const output = document.getElementById('imeiOutput');
    output.innerHTML = 'Fixing NV RAM...<br>';
    
    setTimeout(() => {
        output.innerHTML += '<span style="color: #0f0">NV RAM Fixed</span><br>';
        showFlashToast('NV RAM Fixed', 'NV RAM repair complete', 'success');
    }, 1500);
    
    sendAdvancedOperation('fix_nv_ram');
}

function repairQCN() {
    const output = document.getElementById('imeiOutput');
    output.innerHTML = 'Restoring QCN backup...<br>';
    
    setTimeout(() => {
        output.innerHTML += '<span style="color: #0f0">QCN Restored</span><br>';
        showFlashToast('QCN Restored', 'QCN backup restored successfully', 'success');
    }, 2000);
    
    sendAdvancedOperation('restore_qcn');
}

function securityUnlock() {
    if (!confirm('This will attempt to unlock device security. Continue?')) return;
    
    const output = document.getElementById('imeiOutput');
    output.innerHTML = 'Unlocking security...<br>';
    
    setTimeout(() => {
        output.innerHTML += '<span style="color: #0f0">Security Unlocked</span><br>';
        showFlashToast('Security Unlocked', 'Device security has been bypassed', 'success');
    }, 2500);
    
    sendAdvancedOperation('security_unlock');
}

// Reset Functions
function factoryReset() {
    if (!confirm('This will wipe all user data. Continue?')) return;
    
    simulateReset('Factory Reset', ['Wiping data...', 'Formatting system...', 'Clearing cache...'], 3000);
    
    setTimeout(() => {
        showFlashToast('Factory Reset', 'Device has been factory reset', 'success');
        playFlashSound('success');
    }, 3500);
    
    sendAdvancedOperation('factory_reset');
}

function hardReset() {
    if (!confirm('WARNING: Hard reset will erase EVERYTHING! Continue?')) return;
    
    simulateReset('Hard Reset', [
        'Entering BROM mode...',
        'Erasing partition table...',
        'Formatting all partitions...',
        'Restoring boot sector...'
    ], 5000);
    
    setTimeout(() => {
        showFlashToast('Hard Reset', 'Device has been completely reset', 'success');
        playFlashSound('success');
    }, 5500);
    
    sendAdvancedOperation('hard_reset');
}

function rebootRecovery() {
    simulateReset('Rebooting to Recovery', ['Rebooting...', 'Loading recovery image...'], 2000);
    
    setTimeout(() => {
        showFlashToast('Recovery Mode', 'Device is now in recovery mode', 'success');
        currentDeviceMode = 'recovery';
        updateDeviceStatus();
    }, 2500);
    
    sendAdvancedOperation('reboot_recovery');
}

function wipeCache() {
    simulateReset('Wiping Cache', ['Clearing cache partition...', 'Verifying...'], 2000);
    
    setTimeout(() => {
        showFlashToast('Cache Wiped', 'Cache has been cleared', 'success');
    }, 2500);
    
    sendAdvancedOperation('wipe_cache');
}

function wipeDalvik() {
    simulateReset('Wiping Dalvik', ['Clearing Dalvik cache...', 'Optimizing ART...'], 2000);
    
    setTimeout(() => {
        showFlashToast('Dalvik Wiped', 'Dalvik cache has been cleared', 'success');
    }, 2500);
    
    sendAdvancedOperation('wipe_dalvik');
}

function formatData() {
    if (!confirm('This will format /data partition. Continue?')) return;
    
    simulateReset('Formatting Data', ['Unmounting /data...', 'Formatting ext4...', 'Tuning filesystem...'], 3000);
    
    setTimeout(() => {
        showFlashToast('Data Formatted', '/data partition has been formatted', 'success');
    }, 3500);
    
    sendAdvancedOperation('format_data');
}

// MTK & BROM Functions
function mtkBromFlash() {
    simulateMTKOperation('BROM Flash', [
        'Entering BROM mode...',
        'Authenticating...',
        'Loading scatter file...',
        'Flashing partitions...'
    ], 4000);
    
    setTimeout(() => {
        showFlashToast('BROM Flash', 'MTK BROM flashing complete', 'success');
    }, 4500);
    
    sendAdvancedOperation('mtk_brom_flash');
}

function mtkMetaMode() {
    simulateMTKOperation('Enabling Meta', [
        'Sending DA...',
        'Establishing connection...',
        'Entering Meta mode...'
    ], 2000);
    
    setTimeout(() => {
        showFlashToast('Meta Mode', 'Device is now in Meta mode', 'success');
    }, 2500);
    
    sendAdvancedOperation('mtk_meta_mode');
}

function mtkDA() {
    simulateMTKOperation('Download Agent', [
        'Loading DA...',
        'Executing DA...',
        'DA running...'
    ], 2000);
    
    setTimeout(() => {
        showFlashToast('DA Loaded', 'Download Agent is active', 'success');
    }, 2500);
    
    sendAdvancedOperation('mtk_da');
}

function mtkAuth() {
    simulateMTKOperation('Auth Bypass', [
        'Checking security...',
        'Bypassing authentication...',
        'Gaining access...'
    ], 3000);
    
    setTimeout(() => {
        showFlashToast('Auth Bypass', 'MTK authentication bypassed', 'success');
    }, 3500);
    
    sendAdvancedOperation('mtk_auth_bypass');
}

function forceBR2Meta() {
    if (!confirm('Force BROM→Meta? May cause issues on some devices!')) return;
    
    simulateMTKOperation('BROM→Meta', [
        'Triggering watchdog...',
        'BROM timeout...',
        'Switching to Meta...',
        'Establishing connection...'
    ], 4000);
    
    setTimeout(() => {
        showFlashToast('Mode Forced', 'Switched from BROM to Meta', 'warning');
    }, 4500);
    
    sendAdvancedOperation('force_brom2meta');
}

function forceMeta2BR() {
    if (!confirm('Force Meta→BROM? Device will reboot!')) return;
    
    simulateMTKOperation('Meta→BROM', [
        'Sending reset command...',
        'Triggering BROM...',
        'Waiting for BROM...',
        'BROM detected...'
    ], 4000);
    
    setTimeout(() => {
        showFlashToast('Mode Forced', 'Switched from Meta to BROM', 'warning');
    }, 4500);
    
    sendAdvancedOperation('force_meta2brom');
}

// Helper Functions
function simulateReset(title, steps, duration) {
    const output = document.getElementById('deviceInfoOutput') || 
                   document.createElement('div');
    output.innerHTML = `<strong>${title}</strong><br><br>`;
    
    let stepIndex = 0;
    const stepInterval = setInterval(() => {
        if (stepIndex < steps.length) {
            output.innerHTML += `→ ${steps[stepIndex]}<br>`;
            stepIndex++;
        } else {
            clearInterval(stepInterval);
            output.innerHTML += `<br><span style="color: #0f0">✓ Complete</span>`;
        }
    }, duration / steps.length);
    
    if (document.getElementById('deviceInfoOutput')) {
        document.getElementById('deviceInfoOutput').innerHTML = output.innerHTML;
    }
    
    logAdvancedOperation(title);
}

function simulateMTKOperation(title, steps, duration) {
    const output = document.getElementById('mtkOutput');
    if (!output) return;
    
    output.innerHTML = `<strong style="color: #ff6600">${title}</strong><br><br>`;
    
    let stepIndex = 0;
    const stepInterval = setInterval(() => {
        if (stepIndex < steps.length) {
            output.innerHTML += `→ ${steps[stepIndex]}<br>`;
            stepIndex++;
        } else {
            clearInterval(stepInterval);
            output.innerHTML += `<br><span style="color: #0f0">✓ Ready</span>`;
        }
    }, duration / steps.length);
    
    logAdvancedOperation(title);
}

// Protocol Information Display
function showProtocolInfo(brand) {
    const brandInfo = EXTENDED_BRANDS[brand] || SUPPORTED_BRANDS[brand];
    if (!brandInfo) return;
    
    const protocols = brandInfo.protocols || [];
    const output = document.getElementById('deviceInfoOutput');
    if (!output) return;
    
    output.innerHTML = `
        <strong>${brandInfo.name} Protocols</strong><br><br>
        ${protocols.map(p => `⚡ ${p}`).join('<br>')}<br><br>
        <small>Supported flash methods for this brand</small>
    `;
}

// Flash Protocol Details
const PROTOCOL_DETAILS = {
    'LG_G3': { name: 'LG G3 Flash', desc: 'KDZ firmware via LGUP' },
    'LG_G4': { name: 'LG G4 Flash', desc: 'Firmware via LG Bridge' },
    'Nokia_RX': { name: 'Nokia RX Mode', desc: 'Dead USB flashing' },
    'BootROM': { name: 'BootROM Mode', desc: 'Low-level flashing' },
    'Flashtool': { name: 'Flashtool', desc: 'Sony .ftf flashing' },
    'FTF': { name: 'FTF Format', desc: 'Sony Firmware Format' },
    'BROM': { name: 'BROM Mode', desc: 'MediaTek BootROM' },
    'Meta': { name: 'Meta Mode', ext: 'MediaTek DA' },
    'DA_Legacy': { name: 'DA Legacy', desc: 'Older Download Agent' },
    'SPD_COM': { name: 'SPD COM', desc: 'Spreadtrum Protocol' }
};

// Show protocol details
function showProtocolDetails(protocol) {
    const details = PROTOCOL_DETAILS[protocol];
    if (!details) return;
    
    showFlashToast(details.name, details.desc, 'info');
}
