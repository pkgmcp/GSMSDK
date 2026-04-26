# GSMSDK Mobile Repair Dashboard

## Overview

A high-performance, web-based mobile repair dashboard that interfaces directly with Android devices using WebUSB technology. Supports ADB, Fastboot, and Samsung Download Mode operations.

## Features

### Multi-Tab Interface

1. **Device Info (Home)**
   - Auto-detects connected devices
   - Shows Model, Serial, and Knox status (for Samsung)
   - Real-time battery level monitoring

2. **ADB Toolset**
   - Terminal output with xterm.js integration
   - One-click actions:
     - Reboot Recovery/Bootloader
     - Sideload OTA
     - Remove FRP (MTP)
   - Quick command execution

3. **Fastboot Flasher**
   - Partition table selector
   - Drag-and-drop .img uploader
   - Progress bar for chunk-based flashing
   - Supports all major partitions

4. **Samsung Odin Web**
   - Four-file slot system (BL, AP, CP, CSC)
   - Toggle for Auto Reboot and F. Reset Time
   - Real-time "Nand Write" log
   - .tar.md5 firmware support

## Architecture

### WebUSB Integration

- **Device Detection**: Automatic VID-based detection
  - `0x04E8`: Samsung devices
  - `0x18D1`: Google/Pixel devices
  - `0x05C6`: Qualcomm devices

- **GSMSDK Transport Layer**
  - Handles bulk transfers
  - WebWorker-based chunking for large files
  - Maintains 60fps UI responsiveness

### Security Features

- HTTPS-only (browser requirement)
- MD5/SHA256 checksum verification before transfers
- Secure WebUSB permission handling
- Isolated WebWorker execution

## Usage

### Connecting a Device

1. Open the dashboard in Chrome/Edge (HTTPS required)
2. Click "Connect Device"
3. Select your device from the browser prompt
4. Dashboard auto-switches to appropriate tab

### Flashing Firmware

**Fastboot Mode:**
1. Boot device into Fastboot (e.g., `adb reboot bootloader`)
2. Drag and drop .img files to partition slots
3. Click "Flash" to begin transfer

**Samsung Download Mode:**
1. Prepare .tar.md5 firmware package
2. Load files into respective slots (BL, AP, CP, CSC)
3. Configure options (Auto Reboot, F. Reset Time)
4. Click "Start Flash"

### ADB Operations

Execute shell commands directly from the terminal:
```bash
# List packages
pm list packages

# Check battery status
dumpsys battery

# Screenshot
screencap -p /sdcard/screen.png
```

## File Structure

```
dashboard/
├── index.html          # Main dashboard interface
├── dashboard.js        # Core JavaScript logic
└── workers/
    ├── md5.worker.js   # MD5 checksum calculation
    ├── chunk.worker.js # File chunking
    └── progress.worker.js # Progress tracking
```

## API Endpoints

### Device Management
- `GET /api/devices` - List connected devices
- `POST /api/devices/connect` - Connect to device via WebUSB
- `POST /api/devices/{id}/shell` - Execute ADB shell command
- `POST /api/devices/{id}/reboot` - Reboot device

### Flash Operations
- `POST /api/flash` - Flash partition
- `POST /api/samsung/flash` - Flash Samsung firmware
- `GET /api/partitions` - List available partitions

## Browser Support

- Chrome 61+ (recommended)
- Edge 79+
- Opera 48+

**Note**: Firefox does not support WebUSB.

## Performance

- Chunk size: 1MB for optimal transfer speed
- UI updates: 60fps via WebWorkers
- Checksum calculation: Background thread
- Memory usage: < 500MB for 4GB transfers

## Security Warnings

⚠️ **WARNING**: Flashing firmware can brick your device!

- Always verify firmware compatibility
- Ensure battery is charged (> 50%)
- Use official firmware when possible
- GSMSDK is not responsible for device damage

## Troubleshooting

### Device Not Detected
1. Check USB cable (use data cable, not charge-only)
2. Enable USB Debugging on device
3. Try different USB port
4. Grant USB permission in browser prompt

### Flashing Fails
1. Verify partition compatibility
2. Check file integrity (MD5 checksum)
3. Ensure device is in correct mode
4. Try different USB port/cable

### Slow Transfers
1. Use USB 3.0 port
2. Close other USB devices
3. Disable antivirus temporarily
4. Check browser console for errors

## License

MIT License - See GSMSDK project for details

## Support

For issues and questions:
- GitHub: https://github.com/pkgmcp/GSMSDK
- Documentation: https://docs.openclaw.ai
- Discord: https://discord.gg/clawd
