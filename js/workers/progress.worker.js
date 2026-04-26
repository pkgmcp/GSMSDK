/**
 * Progress Tracking Worker
 * 
 * Monitors and calculates transfer progress for smooth UI updates
 * at 60fps without blocking the main thread.
 */

// Progress tracking state
let transfers = new Map();
let animationFrameId = null;
let lastUpdateTime = 0;
const UPDATE_INTERVAL = 1000 / 60; // 60fps

self.onmessage = function(e) {
  const { type, payload } = e.data;

  switch(type) {
    case 'start_transfer':
      startTransfer(payload);
      break;
    case 'update_progress':
      updateProgress(payload);
      break;
    case 'complete_transfer':
      completeTransfer(payload);
      break;
    case 'cancel_transfer':
      cancelTransfer(payload);
      break;
    case 'get_status':
      getStatus(payload);
      break;
  }
};

// Start a new transfer
function startTransfer({ id, totalSize, type, metadata }) {
  transfers.set(id, {
    id,
    type,
    totalSize,
    transferred: 0,
    progress: 0,
    speed: 0,
    eta: 0,
    startTime: Date.now(),
    lastUpdateTime: Date.now(),
    metadata: metadata || {},
    status: 'running',
    chunks: []
  });

  // Start animation loop if not already running
  if (!animationFrameId) {
    animationFrameId = requestAnimationFrame(animationLoop);
  }

  self.postMessage({
    type: 'transfer_started',
    payload: { id, type, totalSize }
  });
}

// Update transfer progress
function updateProgress({ id, transferred, chunkIndex, chunkSize }) {
  const transfer = transfers.get(id);
  if (!transfer) return;

  const now = Date.now();
  const timeDelta = (now - transfer.lastUpdateTime) / 1000; // seconds

  transfer.transferred = transferred;
  transfer.progress = (transferred / transfer.totalSize) * 100;
  
  // Calculate speed
  if (timeDelta > 0) {
    const chunkDelta = chunkSize || (transferred - (transfer.chunks[chunkIndex - 1]?.transferred || 0));
    transfer.speed = chunkDelta / timeDelta; // bytes per second
  }

  // Calculate ETA
  if (transfer.speed > 0) {
    const remainingBytes = transfer.totalSize - transferred;
    transfer.eta = remainingBytes / transfer.speed;
  }

  // Store chunk info
  if (chunkIndex !== undefined) {
    transfer.chunks[chunkIndex] = {
      transferred,
      size: chunkSize,
      timestamp: now
    };
  }

  transfer.lastUpdateTime = now;

  // Update smooth progress
  if (!transfer.smoothProgress) {
    transfer.smoothProgress = transfer.progress;
  }
}

// Complete a transfer
function completeTransfer(id) {
  const transfer = transfers.get(id);
  if (!transfer) return;

  transfer.status = 'completed';
  transfer.progress = 100;
  transfer.transferred = transfer.totalSize;
  transfer.endTime = Date.now();
  transfer.duration = transfer.endTime - transfer.startTime;

  self.postMessage({
    type: 'transfer_completed',
    payload: {
      id,
      duration: transfer.duration,
      averageSpeed: transfer.totalSize / (transfer.duration / 1000)
    }
  });

  // Clean up if no active transfers
  checkCleanup();
}

// Cancel a transfer
function cancelTransfer(id) {
  const transfer = transfers.get(id);
  if (!transfer) return;

  transfer.status = 'cancelled';
  transfer.endTime = Date.now();

  self.postMessage({
    type: 'transfer_cancelled',
    payload: { id }
  });

  checkCleanup();
}

// Get transfer status
function getStatus(id) {
  const transfer = id ? transfers.get(id) : Array.from(transfers.values());
  
  self.postMessage({
    type: 'status',
    payload: transfer
  });
}

// Animation loop for smooth progress updates
function animationLoop(timestamp) {
  const now = timestamp;
  const deltaTime = now - lastUpdateTime;

  if (deltaTime >= UPDATE_INTERVAL) {
    let hasActiveTransfers = false;
    const updates = [];

    for (const [id, transfer] of transfers) {
      if (transfer.status !== 'running') continue;

      hasActiveTransfers = true;

      // Smooth progress interpolation
      if (transfer.smoothProgress < transfer.progress) {
        const diff = transfer.progress - transfer.smoothProgress;
        transfer.smoothProgress += Math.min(diff, 2); // Max 2% per frame
      }

      updates.push({
        id,
        progress: transfer.smoothProgress,
        transferred: transfer.transferred,
        totalSize: transfer.totalSize,
        speed: transfer.speed,
        eta: transfer.eta,
        type: transfer.type
      });
    }

    if (updates.length > 0) {
      self.postMessage({
        type: 'progress_update',
        payload: updates
      });
    }

    lastUpdateTime = now;
  }

  if (hasActiveTransfers) {
    animationFrameId = requestAnimationFrame(animationLoop);
  } else {
    animationFrameId = null;
  }
}

// Check if cleanup is needed
    function checkCleanup() {
      const completedTransfers = Array.from(transfers.values())
        .filter(t => t.status === 'completed' || t.status === 'cancelled');

      // Remove old completed transfers (older than 5 minutes)
      const now = Date.now();
      for (const transfer of completedTransfers) {
        if (now - transfer.endTime > 5 * 60 * 1000) {
          transfers.delete(transfer.id);
        }
      }

      // Stop animation loop if no active transfers
      let hasActive = false;
      for (const transfer of transfers.values()) {
        if (transfer.status === 'running') {
          hasActive = true;
          break;
        }
      }

      if (!hasActive && animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
      }
    }

    // Format bytes to human-readable string
    function formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Format time to human-readable string
    function formatTime(seconds) {
      if (seconds < 60) return Math.floor(seconds) + 's';
      if (seconds < 3600) return Math.floor(seconds / 60) + 'm ' + Math.floor(seconds % 60) + 's';
      return Math.floor(seconds / 3600) + 'h ' + Math.floor((seconds % 3600) / 60) + 'm';
    }

    // Export utility functions
    self.formatBytes = formatBytes;
    self.formatTime = formatTime;

    // Handle errors
    self.onerror = function(error) {
      console.error('Progress worker error:', error);
    };