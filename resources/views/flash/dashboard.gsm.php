@extends('flash.layout')

@section('title')
    Flash Tool Dashboard
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
                        <h1 class="text-2xl font-bold text-white">GSMSDK Flash Tool</h1>
                        <p class="text-blue-100 text-sm">ADB & Fastboot Device Management</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span id="deviceStatus" class="px-4 py-2 bg-green-500 bg-opacity-20 text-green-100 rounded-full text-sm font-medium">
                        Checking devices...
                    </span>
                    <button onclick="location.href='/flash/samsung/download'" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-all flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 9.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm-6.5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm11.704-1.93a1.5 1.5 0 01.183 2.011L16.5 13.5l1.704 1.705a1.5 1.5 0 01-2.12 2.12l-1.705-1.704-1.705 1.704a1.5 1.5 0 01-2.12-2.12L12.12 13.5l-1.704-1.705a1.5 1.5 0 012.12-2.12l1.705 1.704 1.705-1.704a1.5 1.5 0 012.011.183zM12 17a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                        <span>Samsung Flash</span>
                    </button>
                    <button onclick="location.href='/flash'" class="px-4 py-2 bg-white bg-opacity-20 text-white rounded-lg font-medium hover:bg-opacity-30 transition-all">
                        ← Back
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-blue-500 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-500 bg-opacity-20 rounded-lg">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white" id="statTotalDevices">0</h3>
                <p class="text-gray-400 text-sm">Total Devices</p>
            </div>
            
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-green-500 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-500 bg-opacity-20 rounded-lg">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white" id="statOnlineDevices">0</h3>
                <p class="text-gray-400 text-sm">Online</p>
            </div>
            
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-yellow-500 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-yellow-500 bg-opacity-20 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-.833-2.694-.833-3.464 0L3.34 16c-.77.833.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white" id="statUnauthorized">0</h3>
                <p class="text-gray-400 text-sm">Unauthorized</p>
            </div>
            
            <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-purple-500 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-500 bg-opacity-20 rounded-lg">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white" id="statFastboot">0</h3>
                <p class="text-gray-400 text-sm">Fastboot Mode</p>
            </div>
        </div>

        <!-- Flash Operations Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Flash Panel -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-700">
                        <nav class="flex space-x-4 px-6" aria-label="Flash Tabs">
                            <button onclick="switchFlashTab('fastboot')" id="tab-fastboot" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-blue-400 border-blue-500 focus:outline-none">
                                Fastboot Flash
                            </button>
                            <button onclick="switchFlashTab('adb')" id="tab-adb" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-300 focus:outline-none">
                                ADB Tools
                            </button>
                            <button onclick="switchFlashTab('samsung')" id="tab-samsung" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-300 focus:outline-none">
                                Samsung Download
                            </button>
                            <button onclick="switchFlashTab('terminal')" id="tab-terminal" class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-300 focus:outline-none">
                                Terminal
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Fastboot Flash Tab -->
                        <div id="tab-content-fastboot" class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-white mb-2">Flash Partition</h3>
                                <p class="text-gray-400 text-sm">Flash firmware images to device partitions</p>
                            </div>

                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Partition Name</label>
                                    <select id="partitionNameFastboot" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">-- Select partition --</option>
                                        <option value="boot">boot</option>
                                        <option value="system">system</option>
                                        <option value="vendor">vendor</option>
                                        <option value="product">product</option>
                                        <option value="system_ext">system_ext</option>
                                        <option value="recovery">recovery</option>
                                        <option value="vbmeta">vbmeta</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Image File (.img)</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="text" id="imagePathFastboot" class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="/path/to/image.img">
                                        <button onclick="document.getElementById('filePickerFastboot').click()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-all">
                                            Browse
                                        </button>
                                        <input type="file" id="filePickerFastboot" accept=".img,.bin" class="hidden" onchange="document.getElementById('imagePathFastboot').value = this.files[0]?.path || ''">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Slot (A/B)</label>
                                        <select id="slotSelectFastboot" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm">
                                            <option value="all">All slots</option>
                                            <option value="a">Slot A</option>
                                            <option value="b">Slot B</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Verify</label>
                                        <select id="verifySelectFastboot" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm">
                                            <option value="true">Yes</option>
                                            <option value="false">No</option>
                                        </select>
                                    </div>
                                </div>

                                <button onclick="startFastbootFlash()" id="flashBtnFastboot" class="w-full py-3 px-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                    Start Flash Operation
                                </button>
                            </div>

                            <!-- Progress -->
                            <div id="fastbootProgress" class="hidden">
                                <h4 class="text-md font-medium text-white mb-3">Flash Progress</h4>
                                <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-400">Progress</span>
                                        <span id="fastbootProgressText" class="text-sm font-medium text-green-400">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-800 rounded-full h-2">
                                        <div id="fastbootProgressBar" class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ADB Tools Tab -->
                        <div id="tab-content-adb" class="hidden space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-white mb-2">ADB Shell</h3>
                                <p class="text-gray-400 text-sm">Execute shell commands on the device</p>
                            </div>

                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Command</label>
                                    <div class="flex space-x-2">
                                        <input type="text" id="adbCommand" class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="ls -la /sdcard">
                                        <button onclick="executeAdbCommand()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-all">
                                            Execute
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <button onclick="document.getElementById('adbCommand').value='ls -la /sdcard'" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-sm">ls -la /sdcard</button>
                                    <button onclick="document.getElementById('adbCommand').value='pm list packages'" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-sm">pm list packages</button>
                                    <button onclick="document.getElementById('adbCommand').value='dumpsys battery'" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-sm">dumpsys battery</button>
                                    <button onclick="document.getElementById('adbCommand').value='getprop ro.build.version.release'" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-sm">getprop</button>
                                </div>
                            </div>

                            <!-- Output -->
                            <div id="adbOutputArea" class="hidden">
                                <h4 class="text-md font-medium text-white mb-3">Command Output</h4>
                                <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                    <div id="adbOutput" class="font-mono text-sm text-gray-300 max-h-60 overflow-y-auto whitespace-pre-wrap">
                                        <!-- Output will be displayed here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Samsung Download Tab -->
                        <div id="tab-content-samsung" class="hidden">
                            <div class="text-center py-12">
                                <div class="w-20 h-20 mx-auto mb-4 bg-yellow-500 bg-opacity-20 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13 9.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm-6.5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm11.704-1.93a1.5 1.5 0 01.183 2.011L16.5 13.5l1.704 1.705a1.5 1.5 0 01-2.12 2.12l-1.705-1.704-1.705 1.704a1.5 1.5 0 01-2.12-2.12L12.12 13.5l-1.704-1.705a1.5 1.5 0 012.12-2.12l1.705 1.704 1.705-1.704a1.5 1.5 0 012.011.183zM12 17a4 4 0 100-8 4 4 0 000 8z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-white mb-2">Samsung Download Mode Flasher</h3>
                                <p class="text-gray-400 text-sm mb-4">Odin-style .tar.md5 firmware flashing</p>
                                <a href="/flash/samsung/download" class="inline-block px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-all transform hover:scale-[1.02]">
                                    Open Samsung Flasher →
                                </a>
                            </div>
                        </div>

                        <!-- Terminal Tab -->
                        <div id="tab-content-terminal" class="hidden">
                            <div>
                                <h3 class="text-lg font-medium text-white mb-2">Interactive Terminal</h3>
                                <p class="text-gray-400 text-sm">Execute shell commands with history and autocomplete</p>
                            </div>

                            <div class="bg-gray-900 rounded-lg p-0 overflow-hidden">
                                <div class="bg-gray-800 px-4 py-2 border-b border-gray-700 flex items-center justify-between">
                                    <span class="text-sm text-gray-400">ADB Shell</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-green-400">●</span>
                                        <span class="text-xs text-gray-400">Connected</span>
                                    </div>
                                </div>
                                <div id="terminalOutput" class="p-4 font-mono text-sm text-gray-300 max-h-96 overflow-y-auto">
                                    <div class="text-gray-500 mb-2">=== Android Debug Bridge Shell ===</div>
                                    <div class="text-gray-500">Type 'help' for available commands</div>
                                    <div class="text-green-400">$</div>
                                </div>
                                <div class="border-t border-gray-700 bg-gray-800 p-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-green-400">$</span>
                                        <input type="text" id="terminalInput" class="flex-1 bg-transparent text-white text-sm focus:outline-none" placeholder="Type command...">
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Commands -->
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-400 mb-2">Quick Commands</h4>
                                <div class="flex flex-wrap gap-2">
                                    <button onclick="addToTerminal('pm list packages')" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-xs">pm list packages</button>
                                    <button onclick="addToTerminal('dumpsys activity processes')" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-xs">dumpsys activity</button>
                                    <button onclick="addToTerminal('input keyevent KEYCODE_HOME')" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-xs">input keyevent</button>
                                    <button onclick="addToTerminal('screencap -p /sdcard/screen.png')" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-xs">screencap</button>
                                    <button onclick="addToTerminal('logcat -d | head -50')" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded text-xs">logcat</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Panel -->
            <div>
                <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Connected Devices
                    </h3>

                    <div id="devicesList" class="space-y-3">
                        <div class="text-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <p>No devices connected</p>
                            <p class="text-xs mt-1">Connect a device via USB or network</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-700">
                        <button onclick="refreshDevices()" class="w-full py-2 px-4 bg-gray-700 hover:bg-gray-600 text-gray-100 rounded-lg text-sm font-medium transition-all flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Refresh Devices</span>
                        </button>
                    </div>

                    <div class="mt-4 text-xs text-gray-500 text-center">
                        Last updated: <span id="lastUpdated">--:--:--</span>
                    </div>
                </div>

                <!-- Device Actions -->
                <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 p-6 mt-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="location.href='/flash/files'" class="p-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium text-gray-100 transition-all flex flex-col items-center space-y-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            <span>File Manager</span>
                        </button>
                        
                        <button onclick="location.href='/flash/logs'" class="p-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium text-gray-100 transition-all flex flex-col items-center space-y-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span>Logcat</span>
                        </button>
                        
                        <button onclick="location.href='/flash/terminal'" class="p-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium text-gray-100 transition-all flex flex-col items-center space-y-2">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Terminal</span>
                        </button>
                        
                        <button onclick="location.href='/flash/samsung/download'" class="p-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium text-gray-100 transition-all flex flex-col items-center space-y-2">
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 9.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm-6.5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm11.704-1.93a1.5 1.5 0 01.183 2.011L16.5 13.5l1.704 1.705a1.5 1.5 0 01-2.12 2.12l-1.705-1.704-1.705 1.704a1.5 1.5 0 01-2.12-2.12L12.12 13.5l-1.704-1.705a1.5 1.5 0 012.12-2.12l1.705 1.704 1.705-1.704a1.5 1.5 0 012.011.183zM12 17a4 4 0 100-8 4 4 0 000 8z"/>
                            </svg>
                            <span>Samsung</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// State
let terminalHistory = [];
let historyIndex = -1;
let currentCommand = '';

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    refreshDevices();
    loadTerminalHistory();
    
    // Setup terminal input
    const terminalInput = document.getElementById('terminalInput');
    if (terminalInput) {
        terminalInput.addEventListener('keydown', handleTerminalKeydown);
        terminalInput.focus();
    }
    
    // Auto-refresh devices every 30 seconds
    setInterval(refreshDevices, 30000);
});

// Switch flash tabs
function switchFlashTab(tabName) {
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
    
    // Show selected tab
    const contentTab = document.getElementById('tab-content-' + tabName);
    if (contentTab) {
        contentTab.classList.remove('hidden');
    }
    
    // Activate tab button
    const tabButton = document.getElementById('tab-' + tabName);
    if (tabButton) {
        tabButton.classList.remove('border-transparent', 'text-gray-400');
        tabButton.classList.add('border-blue-500', 'text-blue-400');
    }
}

// Refresh device list
function refreshDevices() {
    fetch('/api/devices')
        .then(response => response.json())
        .then(result => {
            updateDeviceStats(result.devices || []);
            updateDeviceList(result.devices || []);
            updateLastUpdated();
        })
        .catch(error => {
            console.error('Error fetching devices:', error);
        });
}

// Update device statistics
function updateDeviceStats(devices) {
    const total = devices.length;
    const online = devices.filter(d => d.online).length;
    const unauthorized = devices.filter(d => !d.authorized).length;
    const fastboot = devices.filter(d => d.type === 'fastboot').length;
    
    document.getElementById('statTotalDevices').textContent = total;
    document.getElementById('statOnlineDevices').textContent = online;
    document.getElementById('statUnauthorized').textContent = unauthorized;
    document.getElementById('statFastboot').textContent = fastboot;
    
    // Update status badge
    const statusBadge = document.getElementById('deviceStatus');
    if (total > 0) {
        if (fastboot > 0) {
            statusBadge.textContent = fastboot + ' in Download Mode';
            statusBadge.className = 'px-4 py-2 bg-yellow-500 bg-opacity-20 text-yellow-100 rounded-full text-sm font-medium';
        } else if (online === total) {
            statusBadge.textContent = 'All devices online';
            statusBadge.className = 'px-4 py-2 bg-green-500 bg-opacity-20 text-green-100 rounded-full text-sm font-medium';
        } else {
            statusBadge.textContent = online + '/' + total + ' online';
            statusBadge.className = 'px-4 py-2 bg-blue-500 bg-opacity-20 text-blue-100 rounded-full text-sm font-medium';
        }
    } else {
        statusBadge.textContent = 'No devices';
        statusBadge.className = 'px-4 py-2 bg-gray-500 bg-opacity-20 text-gray-100 rounded-full text-sm font-medium';
    }
}

// Update device list
function updateDeviceList(devices) {
    const listEl = document.getElementById('devicesList');
    
    if (devices.length === 0) {
        listEl.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p>No devices connected</p>
                <p class="text-xs mt-1">Connect a device via USB or network</p>
            </div>
        `;
        return;
    }
    
    listEl.innerHTML = devices.map(device => `
        <div class="bg-gray-900 rounded-lg p-3 border border-gray-700 hover:border-blue-500 transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-white font-medium truncate">${escapeHtml(device.model || device.serial)}</span>
                <span class="text-xs px-2 py-0.5 rounded ${getDeviceStatusClass(device)}">
                    ${getDeviceStatusText(device)}
                </span>
            </div>
            <div class="text-xs text-gray-400 mono truncate">${escapeHtml(device.serial)}</div>
            <div class="text-xs text-gray-500 mt-1">
                ${device.type.toUpperCase()} • ${device.authorized ? 'Authorized' : 'Unauthorized'}
            </div>
        </div>
    `).join('');
}

// Get device status class
function getDeviceStatusClass(device) {
    if (device.type === 'fastboot') {
        return 'bg-yellow-500 bg-opacity-20 text-yellow-100';
    }
    if (!device.authorized) {
        return 'bg-red-500 bg-opacity-20 text-red-100';
    }
    if (device.online) {
        return 'bg-green-500 bg-opacity-20 text-green-100';
    }
    return 'bg-gray-500 bg-opacity-20 text-gray-100';
}

// Get device status text
}