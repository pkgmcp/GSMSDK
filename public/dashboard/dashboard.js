// Global State
let ws = null;
let deviceConnected = false;
let currentDeviceMode = 'disconnected';
let currentTab = 'device';
let flashQueue = [];
let isFlashing = false;

// Initialize dashboard
function initDashboard() {
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