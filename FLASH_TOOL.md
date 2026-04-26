# 🔧 GSMSDK Flash Tool - Complete ADB & Fastboot Command Reference

## Overview

The GSMSDK Flash Tool provides comprehensive Android device management through ADB (Android Debug Bridge) and Fastboot protocols. This document covers all available commands, usage examples, and implementation details.

## 📋 Table of Contents

1. [ADB Commands](#adb-commands)
2. [Fastboot Commands](#fastboot-commands)
3. [Device Management](#device-management)
4. [Recovery Operations](#recovery-operations)
5. [Security Features](#security-features)
6. [API Endpoints](#api-endpoints)
7. [Usage Examples](#usage-examples)

---

## 🎯 ADB Commands

### Device Information

#### List Connected Devices
```bash
adb devices
```
**GSMSDK API:** `GET /api/devices`
```php
$response = $adb->listDevices();
// Returns: [['id' => 'emulator-5554', 'state' => 'device', ...]]
```

#### Get Device Properties
```bash
adb -s <serial> shell getprop
```
**GSMSDK API:** `GET /api/devices/{id}/properties`
```php
$properties = $adb->getProperties('emulator-5554');
```

#### Device State
```bash
adb get-state
```
**GSMSDK API:** `GET /api/devices/{id}/state`

---

### Shell Commands

#### Execute Shell Command
```bash
adb shell ls /system
adb shell pm list packages
adb shell dumpsys battery
```
**GSMSDK API:** `POST /api/devices/{id}/shell`
```php
$result = $adb->shell('ls /sdcard');
// Returns: ['output' => '...', 'exit_code' => 0]
```

#### Interactive Shell
```bash
adb shell
```
**GSMSDK Web Interface:** `/admin/terminal`
```php
$terminal = $adb->interactiveShell();
// Provides persistent shell session
```

#### Multiple Commands
```bash
adb shell <<EOF
cd /sdcard
ls -la
grep "test" file.txt
EOF
```
**GSMSDK API:** Batch execution supported
```php
$adb->batchShell([
    'cd /sdcard',
    'ls -la',
    'grep "test" file.txt'
]);
```

---

### File Transfer

#### Push File to Device
```bash
adb push local_file.txt /sdcard/
adb push app.apk /data/local/tmp/
```
**GSMSDK API:** `POST /api/adb/push`
```php
$result = $adb->pushFile('/local/app.apk', '/sdcard/app.apk');
// Returns: ['success' => true, 'bytes' => 123456]
```

#### Pull File from Device
```bash
adb pull /sdcard/file.txt local_dir/
adb pull /system/build.prop .
```
**GSMSDK API:** `POST /api/adb/pull`
```php
$result = $adb->pullFile('/sdcard/photo.jpg', '/local/photos/');
```

#### Sync Files
```bash
adb sync system
adb sync vendor
```
**GSMSDK API:** `POST /api/adb/sync`

---

### Application Management

#### Install APK
```bash
adb install app.apk
adb install -r app.apk          # Reinstall
adb install -d app.apk          # Allow version downgrade
adb install -g app.apk          # Grant all permissions
adb install -t app.apk          # Allow test packages
```
**GSMSDK API:** `POST /api/devices/{id}/install`
```php
$result = $adb->installApp('/path/to/app.apk', [
    'reinstall' => true,
    'grantPermissions' => true
]);
```

#### Uninstall Application
```bash
adb uninstall com.example.app
adb uninstall -k com.example.app  # Keep data
```
**GSMSDK API:** `DELETE /api/devices/{id}/apps/{package}`
```php
$result = $adb->uninstallApp('com.example.app');
```

#### List Installed Packages
```bash
adb shell pm list packages
adb shell pm list packages -f    # Show APK paths
adb shell pm list packages -3    # Third-party only
```
**GSMSDK API:** `GET /api/devices/{id}/packages`

#### Clear App Data
```bash
adb shell pm clear com.example.app
```

#### Force Stop App
```bash
adb shell am force-stop com.example.app
```

---

### Screen Capture

#### Take Screenshot
```bash
adb shell screencap /sdcard/screenshot.png
adb pull /sdcard/screenshot.png .
```
**GSMSDK API:** `POST /api/devices/{id}/screenshot`
```php
$screenshot = $adb->screenCapture();
// Returns: ['format' => 'png', 'data' => 'base64...']
```

#### Record Screen
```bash
adb shell screenrecord /sdcard/recording.mp4
adb pull /sdcard/recording.mp4 .
```
**GSMSDK API:** `POST /api/devices/{id}/screenrecord`

---

### Logcat

#### View Logs
```bash
adb logcat                    # All logs
adb logcat -s "ActivityManager"  # Filter by tag
adb logcat *:E                 # Errors only
adb logcat -d                 # Dump and exit
adb logcat -c                 # Clear buffer
```
**GSMSDK API:** `GET /api/devices/{id}/logcat`
```php
$logs = $adb->readLogcat([
    'filter' => 'ActivityManager',
    'level' => 'E',
    'lines' => 1000
]);
```

#### Save Logs to File
```bash
adb logcat -f /sdcard/logs.txt
```
**GSMSDK API:** `POST /api/devices/{id}/logcat/save`

---

### Port Forwarding

#### Forward Local to Device
```bash
adb forward tcp:8080 tcp:8080
adb forward tcp:5037 jdwp:1234
```
**GSMSDK API:** `POST /api/devices/{id}/forward`
```php
$adb->forwardPort(8080, 8080);
```

#### Reverse Forward (Device to Local)
```bash
adb reverse tcp:8080 tcp:8080
```

---

### Network

#### Connect to Device over TCP/IP
```bash
adb connect 192.168.1.100:5555
adb disconnect 192.168.1.100:5555
```
**GSMSDK API:** `POST /api/devices/connect`
```php
$adb->connectTcp('192.168.1.100:5555');
```

#### USB Mode
```bash
adb usb
```

---

### Backup & Restore

#### Create Backup
```bash
adb backup -apk -shared -all -f backup.ab
adb backup -noapk com.example.app
```
**GSMSDK API:** `POST /api/devices/{id}/backup`

#### Restore Backup
```bash
adb restore backup.ab
```
**GSMSDK API:** `POST /api/devices/{id}/restore`

---

### Reboot

#### Normal Reboot
```bash
adb reboot
```
**GSMSDK API:** `POST /api/devices/{id}/reboot`

#### Reboot to Bootloader
```bash
adb reboot bootloader
```
**GSMSDK API:** `POST /api/devices/{id}/reboot/bootloader`

#### Reboot to Recovery
```bash
adb reboot recovery
```
**GSMSDK API:** `POST /api/devices/{id}/reboot/recovery`

---

### Root & Remount

#### Root Access
```bash
adb root     # Restart adbd as root
adb unroot   # Restart adbd as non-root
```
**GSMSDK API:** `POST /api/devices/{id}/root`

#### Remount System Partition
```bash
adb remount   # Remount /system as writable
```
**GSMSDK API:** `POST /api/devices/{id}/remount`

---

### Bug Report

```bash
adb bugreport bugreport.zip
```
**GSMSDK API:** `GET /api/devices/{id}/bugreport`

---

## 🚀 Fastboot Commands

### Device Detection

#### List Fastboot Devices
```bash
fastboot devices
```
**GSMSDK API:** `GET /api/devices?mode=fastboot`
```php
$devices = $fastboot->listDevices();
```

#### Get Device Variables
```bash
fastboot getvar all
fastboot getvar product
fastboot getvar version-bootloader
```
**GSMSDK API:** `GET /api/devices/{id}/variables`
```php
$vars = $fastboot->getVariable('product');
```

---

### Flashing Partitions

#### Flash Boot Partition
```bash
fastboot flash boot boot.img
```
**GSMSDK API:** `POST /api/flash`
```php
$result = $fastboot->flash('boot', '/path/to/boot.img');
```

#### Flash System Partition
```bash
fastboot flash system system.img
fastboot flash system system.img --slot=all
```
**GSMSDK API:** `POST /api/flash/system`

#### Flash Vendor Partition
```bash
fastboot flash vendor vendor.img
```
**GSMSDK API:** `POST /api/flash/vendor`

#### Flash Product Partition
```bash
fastboot flash product product.img
```

#### Flash Recovery Partition
```bash
fastboot flash recovery recovery.img
```
**GSMSDK API:** `POST /api/flash/recovery`

#### Flash VBMeta Partition
```bash
fastboot flash vbmeta vbmeta.img
```

#### Flash All Dynamic Partitions
```bash
fastboot flash super super.img
```

---

### Erase Partitions

#### Erase Boot Partition
```bash
fastboot erase boot
```
**GSMSDK API:** `DELETE /api/flash/boot`

#### Erase System Partition
```bash
fastboot erase system
```

#### Erase Data Partition
```bash
fastboot erase userdata
fastboot erase metadata
```

---

### Boot from Image

#### Boot Without Flashing
```bash
fastboot boot boot.img
```
**GSMSDK API:** `POST /api/fastboot/boot`
```php
$fastboot->bootImage('/path/to/boot.img');
```

---

### Lock/Unlock Bootloader

#### Lock Bootloader
```bash
fastboot flashing lock
fastboot oem lock
```
**GSMSDK API:** `POST /api/fastboot/lock`
```php
$fastboot->lockBootloader();
```

#### Unlock Bootloader
```bash
fastboot flashing unlock
fastboot oem unlock
```
**GSMSDK API:** `POST /api/fastboot/unlock`

#### Check Lock Status
```bash
fastboot getvar unlocked
```

---

### Reboot Commands

#### Normal Reboot
```bash
fastboot reboot
```
**GSMSDK API:** `POST /api/fastboot/reboot`

#### Reboot to Bootloader
```bash
fastboot reboot-bootloader
```

---

### OEM Commands

#### OEM Unlock
```bash
fastboot oem device-info
```

#### OEM Config
```bash
fastboot oem <command>
```

---

### Advanced Flashing

#### Sparse Image Flashing
```bash
fastboot flash:raw boot.img
```

#### Disable Verification
```bash
fastboot flashing lock_critical
```

#### Resize Logical Partition
```bash
fastboot resize-logical-partition product 4G
```

---

## 🔐 Recovery Operations

### Standard Recovery

#### Reboot to Recovery
```bash
adb reboot recovery
fastboot reboot recovery
```
**GSMSDK API:** `POST /api/devices/{id}/reboot/recovery`

#### Apply Update from ADB
```bash
adb sideload update.zip
```
**GSMSDK API:** `POST /api/devices/{id}/sideload`

#### Apply Update from SD Card
```bash
adb push update.zip /sdcard/
# Then select in recovery menu
```

### Custom Recovery (TWRP)

#### Flash TWRP
```bash
fastboot flash recovery twrp.img
fastboot boot twrp.img
```

#### Backup via TWRP
```bash
adb shell twrp backup SDBO
```

#### Restore via TWRP
```bash
adb shell twrp restore SDRO
```

---

## 🔒 Security Features

### ADB Security

#### Enable/Disable ADB
```bash
adb kill-server    # Stop ADB daemon
adb start-server   # Start ADB daemon
```

#### Authorize Device
```bash
adb devices        # Check authorization
adb tcpip 5555     # Enable TCP/IP mode
```

#### Revoke Authorizations
```bash
adb kill-server
rm ~/.android/adbkey
adb start-server
```

### Fastboot Security

#### Verify Flash
```bash
fastboot verify boot.img
```

#### Check Lock Status
```bash
fastboot getvar unlocked
fastboot getvar secure
```

### GSMSDK Security

#### CSRF Protection
```php
@csrf  // In templates
$token = $auth->csrfToken();  // In APIs
```

#### Rate Limiting
```php
// Config: 60 requests/minute
$result = $auth->rateLimitMiddleware($request, 'api');
```

#### Authentication Middleware
```php
$router->middleware(['auth']);
```

---

## 🌐 API Endpoints

### ADB Operations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/devices` | List all devices |
| GET | `/api/devices/{id}` | Get device details |
| POST | `/api/devices/{id}/shell` | Execute shell command |
| POST | `/api/devices/{id}/install` | Install APK |
| POST | `/api/devices/{id}/reboot` | Reboot device |
| POST | `/api/devices/{id}/screenshot` | Take screenshot |
| POST | `/api/devices/{id}/logcat` | Get logcat |
| POST | `/api/adb/push` | Push file to device |
| POST | `/api/adb/pull` | Pull file from device |

### Fastboot Operations

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/flash` | Flash partition |
| DELETE | `/api/flash/{partition}` | Erase partition |
| POST | `/api/fastboot/boot` | Boot from image |
| POST | `/api/fastboot/lock` | Lock bootloader |
| POST | `/api/fastboot/unlock` | Unlock bootloader |

### Device Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/devices/connect` | Connect over TCP/IP |
| POST | `/api/devices/{id}/reboot/bootloader` | Reboot to bootloader |
| POST | `/api/devices/{id}/reboot/recovery` | Reboot to recovery |

---

## 💻 Usage Examples

### JavaScript/TypeScript

```typescript
// Install APK
const installApp = async (deviceId: string, apkPath: string) => {
  const response = await fetch(`/api/devices/${deviceId}/install`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ apk_path: apkPath })
  });
  return response.json();
};

// Execute shell command
const executeShell = async (deviceId: string, command: string) => {
  const response = await fetch(`/api/devices/${deviceId}/shell`, {
    method: 'POST',
    body: JSON.stringify({ command })
  });
  return response.json();
};
```

### Python

```python
import requests

# List devices
response = requests.get('http://localhost:8000/api/devices')
devices = response.json()

# Install APK
response = requests.post(
    'http://localhost:8000/api/devices/emulator-5554/install',
    json={'apk_path': 'app.apk'}
)

# Execute shell
response = requests.post(
    'http://localhost:8000/api/devices/emulator-5554/shell',
    json={'command': 'ls /sdcard'}
)
print(response.json())
```

### PHP

```php
use GSMSDK\Core\Application;
use GSMSDK\ADB\ADBDevice;

$app = new Application([]);
$adb = new ADBDevice('emulator-5554');

// Install APK
$adb->installApp('/path/to/app.apk');

// Execute shell
$output = $adb->shell('ls /sdcard');

// Take screenshot
$screenshot = $adb->screenCapture();

// Reboot
$adb->reboot();
```

---

## ⚠️ Warnings & Best Practices

### ADB Warnings

1. **Always backup before flashing**
2. **Verify APK signatures before installation**
3. **Use trusted sources for system images**
4. **Keep ADB version updated**
5. **Revoke USB debugging authorizations when not in use**

### Fastboot Warnings

1. **Incorrect flashing can brick device**
2. **Always verify partition names**
3. **Keep bootloader unlock token safe**
4. **Test on emulator before physical device**
5. **Maintain backup of all partitions**

### GSMSDK Best Practices

1. **Enable HTTPS in production**
2. **Use strong API tokens**
3. **Implement rate limiting**
4. **Monitor API usage**
5. **Keep dependencies updated**
6. **Regular security audits**

---

## 📚 Resources

- [Android Developer Documentation](https://developer.android.com/studio/command-line/adb)
- [Fastboot Documentation](https://source.android.com/docs/setup/reference/adb)
- [GSMSDK Documentation](GSMSDK.md)
- [API Documentation](/api/docs)

---

## 🔧 Troubleshooting

### ADB Not Recognizing Device

```bash
adb kill-server
adb start-server
adb devices
```

### Fastboot Device Not Found

```bash
fastboot devices
# Check USB connection
fastboot reboot-bootloader
```

### Permission Denied

```bash
sudo adb kill-server
sudo adb start-server
```

### Insufficient Storage

```bash
adb shell df
adb shell pm list packages -f
```

---

## 📝 Changelog

### v2.0.0
- Complete ADB & Fastboot implementation
- Web-based flash tool
- RESTful API
- Security enhancements
- Comprehensive documentation

---

**Last Updated:** April 2026  
**Version:** 2.0.0  
**License:** MIT  

---

*For support, visit: https://github.com/pkgmcp/GSMSDK/issues*