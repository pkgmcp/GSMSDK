<?php
/**
 * File Upload Page
 * Premium & Subscription Management
 * GridCN Design System
 */
?>

@extends('layouts.admin.base')

@section('title', 'File Upload & Subscription Management')

@section('content')

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-left">
                <h1 class="glitch" data-text="Premium Upload Center">Premium Upload Center</h1>
                <p class="typewriter-text">Upload firmware, manage subscriptions, access premium features</p>
                <div class="hero-buttons">
                    <a href="#upload-section" class="btn btn-primary btn-large">
                        <i class="icon-upload"></i> Start Upload
                    </a>
                    <a href="#subscription-section" class="btn btn-secondary btn-large">
                        <i class="icon-crown"></i> Premium Plans
                    </a>
                </div>
            </div>
            <div class="hero-right">
                <div class="floating-animations">
                    <svg class="upload-svg" viewBox="0 0 200 200">
                        <circle class="float-1" cx="100" cy="100" r="60" fill="rgba(0,255,136,0.2)"/>
                        <polygon class="float-2" points="100,40 140,120 60,120" fill="rgba(255,0,170,0.3)"/>
                        <rect class="float-3" x="70" y="70" width="60" height="60" fill="rgba(0,255,136,0.4)"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Bar -->
<div class="stats-bar">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-value" data-count="{{ $totalFiles }}">0</div>
                <div class="stat-label">Total Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⬇️</div>
                <div class="stat-value" data-count="{{ $totalDownloads }}">0</div>
                <div class="stat-label">Downloads</div>
            </div>
            <div class="stat-card premium">
                <div class="stat-icon">👑</div>
                <div class="stat-value" data-count="{{ $premiumUsers }}">0</div>
                <div class="stat-label">Premium Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value" data-count="{{ $revenue }}">0</div>
                <div class="stat-label">Revenue (USD)</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container">
    
    <!-- Upload Section -->
    <section id="upload-section" class="card upload-section">
        <div class="card-header">
            <h2><i class="icon-upload"></i> File Upload Center</h2>
            <span class="badge badge-primary">Premium Feature</span>
        </div>
        
        <div class="card-body">
            
            <!-- Subscription Check -->
            @if(!auth()->user()->hasActiveSubscription())
                <div class="subscription-warning">
                    <i class="icon-warning"></i>
                    <h3>Subscription Required</h3>
                    <p>You need an active premium subscription to upload files.</p>
                    <a href="#subscription-section" class="btn btn-primary">Get Premium</a>
                </div>
            @else
                
                <!-- Upload Form -->
                <form id="uploadForm" class="upload-form" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- File Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-file"></i> Select Files to Upload
                            <span class="required">*</span>
                        </label>
                        <div class="drop-zone" id="dropZone">
                            <input type="file" id="fileInput" name="files[]" multiple 
                                   accept=".zip,.rar,.7z,.tar,.gz,.bin,.img,.iso">
                            <div class="drop-zone-content">
                                <i class="icon-cloud-upload"></i>
                                <h3>Drag & Drop Files Here</h3>
                                <p>or click to browse</p>
                                <small>Supported: ZIP, RAR, 7Z, TAR, GZ, BIN, IMG, ISO</small>
                            </div>
                        </div>
                        <div id="fileList" class="file-list"></div>
                    </div>
                    
                    <!-- Upload Type Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-category"></i> Upload Type
                            <span class="required">*</span>
                        </label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="upload_type" value="firmware" checked>
                                <span class="radio-custom"></span>
                                <span class="radio-label">
                                    <strong>Firmware</strong>
                                    <span>Device firmware files</span>
                                </span>
                            </label>
                            
                            <label class="radio-option">
                                <input type="radio" name="upload_type" value="tool">
                                <span class="radio-custom"></span>
                                <span class="radio-label">
                                    <strong>Tool/Software</strong>
                                    <span>Flashing tools and utilities</span>
                                </span>
                            </label>
                            
                            <label class="radio-option">
                                <input type="radio" name="upload_type" value="document">
                                <span class="radio-custom"></span>
                                <span class="radio-label">
                                    <strong>Documentation</strong>
                                    <span>Guides and manuals</span>
                                </span>
                            </label>
                            
                            <label class="radio-option premium-only">
                                <span class="premium-badge">Premium</span>
                                <input type="radio" name="upload_type" value="proprietary">
                                <span class="radio-custom"></span>
                                <span class="radio-label">
                                    <strong>Proprietary Firmware</strong>
                                    <span>Manufacturer exclusive files</span>
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Device Information -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-device"></i> Device Information
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="brand" class="form-control" 
                                       placeholder="Brand (e.g., Xiaomi, Samsung)">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="model" class="form-control" 
                                       placeholder="Model (e.g., Redmi Note 12)">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Version & Region -->
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="version" class="form-control" 
                                       placeholder="Version (e.g., V14.0.5.0)">
                            </div>
                            <div class="col-md-4">
                                <select name="region" class="form-control">
                                    <option value="">Select Region</option>
                                    <option value="global">Global</option>
                                    <option value="china">China</option>
                                    <option value="india">India</option>
                                    <option value="europe">Europe</option>
                                    <option value="usa">USA</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="android_version" class="form-control" 
                                       placeholder="Android Version (e.g., 14)">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Features -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-shield"></i> Security Features
                        </label>
                        <div class="checkbox-group">
                            <label class="checkbox-option">
                                <input type="checkbox" name="imei_repair" value="1">
                                <span class="checkbox-custom"></span>
                                <span>IMEI Repair Supported</span>
                            </label>
                            
                            <label class="checkbox-option">
                                <input type="checkbox" name="frp_remove" value="1">
                                <span class="checkbox-custom"></span>
                                <span>FRP Removal Supported</span>
                            </label>
                            
                            <label class="checkbox-option premium-only">
                                <span class="premium-badge">Premium</span>
                                <input type="checkbox" name="exclusive" value="1">
                                <span class="checkbox-custom"></span>
                                <span>Exclusive/Unreleased Firmware</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- File Category -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-folder"></i> Category
                        </label>
                        <select name="category" class="form-control">
                            <option value="firmware">Firmware</option>
                            <option value="stock">Stock ROM</option>
                            <option value="custom">Custom ROM</option>
                            <option value="recovery">Recovery</option>
                            <option value="bootloader">Bootloader</option>
                            <option value="tools">Tools/Software</option>
                            <option value="documentation">Documentation</option>
                        </select>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-description"></i> Description
                        </label>
                        <textarea name="description" class="form-control" rows="4"
                                  placeholder="Describe this file, known issues, installation instructions..."></textarea>
                    </div>
                    
                    <!-- Tags -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-tag"></i> Tags (comma separated)
                        </label>
                        <input type="text" name="tags" class="form-control" 
                               placeholder="fastboot, adb, repair, root">
                    </div>
                    
                    <!-- Visibility Options -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="icon-eye"></i> Visibility
                        </label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="public" checked>
                                <span class="radio-custom"></span>
                                <span class="radio-label">Public - Anyone can see and download</span>
                            </label>
                            
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="premium">
                                <span class="radio-custom"></span>
                                <span class="radio-label">Premium Only - Requires subscription</span>
                            </label>
                            
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="private">
                                <span class="radio-custom"></span>
                                <span class="radio-label">Private - Only you can see</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large" id="uploadBtn">
                            <i class="icon-upload"></i> Upload Files
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearBtn">
                            <i class="icon-clear"></i> Clear All
                        </button>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="upload-progress" id="uploadProgress" style="display: none;">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-info">
                            <span id="progressText">0%</span>
                            <span id="progressSpeed"></span>
                        </div>
                    </div>
                    
                </form>
                
            @endif
            
        </div>
    </section>
    
    <!-- Premium Subscription Section -->
    <section id="subscription-section" class="card subscription-section">
        <div class="card-header">
            <h2><i class="icon-crown"></i> Premium Subscription Plans</h2>
            <span class="badge badge-gold">Most Popular</span>
        </div>
        
        <div class="card-body">
            
            <!-- Subscription Toggle -->
            <div class="subscription-toggle">
                <span>Monthly</span>
                <label class="switch">
                    <input type="checkbox" id="billingToggle">
                    <span class="slider"></span>
                </label>
                <span>Annual <span class="save-badge">Save 20%</span></span>
            </div>
            
            <!-- Plans Grid -->
            <div class="plans-grid" id="plansContainer">
                
                <!-- Basic Plan -->
                <div class="plan-card" data-plan="basic">
                    <div class="plan-header">
                        <h3>Basic</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount" data-monthly="9.99" data-annual="95.88">9.99</span>
                            <span class="period">/month</span>
                        </div>
                        <p class="plan-description">Perfect for casual users</p>
                    </div>
                    
                    <div class="plan-features">
                        <ul>
                            <li><i class="icon-check"></i> 50 uploads/month</li>
                            <li><i class="icon-check"></i> 500 downloads/month</li>
                            <li><i class="icon-check"></i> Standard support</li>
                            <li><i class="icon-check"></i> 10GB storage</li>
                            <li><i class="icon-times"></i> IMEI repair tools</li>
                            <li><i class="icon-times"></i> FRP removal</li>
                            <li><i class="icon-times"></i> Priority support</li>
                        </ul>
                    </div>
                    
                    <button class="btn btn-outline" data-plan="basic">Select Plan</button>
                </div>
                
                <!-- Pro Plan (Popular) -->
                <div class="plan-card popular" data-plan="pro">
                    <div class="popular-badge">MOST POPULAR</div>
                    <div class="plan-header">
                        <h3>Pro</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount" data-monthly="29.99" data-annual="287.88">29.99</span>
                            <span class="period">/month</span>
                        </div>
                        <p class="plan-description">For power users and developers</p>
                    </div>
                    
                    <div class="plan-features">
                        <ul>
                            <li><i class="icon-check"></i> Unlimited uploads</li>
                            <li><i class="icon-check"></i> Unlimited downloads</li>
                            <li><i class="icon-check"></i> Priority support</li>
                            <li><i class="icon-check"></i> 100GB storage</li>
                            <li><i class="icon-check"></i> IMEI repair tools</li>
                            <li><i class="icon-check"></i> FRP removal</li>
                            <li><i class="icon-check"></i> Advanced analytics</li>
                        </ul>
                    </div>
                    
                    <button class="btn btn-primary" data-plan="pro">Select Plan</button>
                </div>
                
                <!-- Enterprise Plan -->
                <div class="plan-card" data-plan="enterprise">
                    <div class="plan-header">
                        <h3>Enterprise</h3>
                        <div class="price">
                            <span class="currency">$</span>
                            <span class="amount" data-monthly="99.99" data-annual="959.88">99.99</span>
                            <span class="period">/month</span>
                        </div>
                        <p class="plan-description">For businesses and teams</p>
                    </div>
                    
                    <div class="plan-features">
                        <ul>
                            <li><i class="icon-check"></i> Everything in Pro</li>
                            <li><i class="icon-check"></i> Unlimited storage</li>
                            <li><i class="icon-check"></i> 24/7 dedicated support</li>
                            <li><i class="icon-check"></i> Team management</li>
                            <li><i class="icon-check"></i> Custom integration</li>
                            <li><i class="icon-check"></i> SLA guarantee</li>
                            <li><i class="icon-check"></i> API access</li>
                        </ul>
                    </div>
                    
                    <button class="btn btn-outline" data-plan="enterprise">Select Plan</button>
                </div>
                
            </div>
            
            <!-- Payment Methods -->
            <div class="payment-methods">
                <h4>Secure Payment Methods</h4>
                <div class="payment-icons">
                    <i class="icon-stripe">Stripe</i>
                    <i class="icon-paypal">PayPal</i>
                    <i class="icon-apple-pay">Apple Pay</i>
                    <i class="icon-google-pay">Google Pay</i>
                    <i class="icon-credit-card">Credit Card</i>
                </div>
                <p class="security-note">
                    <i class="icon-lock"></i> All payments are encrypted and secure
                </p>
            </div>
            
            <!-- Current Subscription -->
            @if(auth()->user()->hasActiveSubscription())
                <div class="current-subscription">
                    <h3><i class="icon-calendar"></i> Your Current Plan</h3>
                    <div class="subscription-details">
                        <div class="detail-row">
                            <span>Plan:</span>
                            <strong>{{ auth()->user()->subscription->plan_name }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Status:</span>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <div class="detail-row">
                            <span>Renews:</span>
                            <span>{{ auth()->user()->subscription->next_billing_date }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Storage Used:</span>
                            <span>{{ auth()->user()->storage_used }} / {{ auth()->user()->storage_limit }}</span>
                        </div>
                    </div>
                    <div class="subscription-actions">
                        <button class="btn btn-secondary">Manage Subscription</button>
                        <button class="btn btn-outline">Cancel</button>
                    </div>
                </div>
            @endif
            
        </div>
    </section>
    
    <!-- Premium Features Comparison -->
    <section class="card features-comparison">
        <div class="card-header">
            <h2><i class="icon-table"></i> Feature Comparison</h2>
        </div>
        
        <div class="card-body">
            <div class="comparison-table">
                <div class="table-header">
                    <div>Features</div>
                    <div>Free</div>
                    <div>Basic</div>
                    <div class="popular">Pro</div>
                    <div>Enterprise</div>
                </div>
                
                <div class="table-row">
                    <div>Uploads per month</div>
                    <div>5</div>
                    <div>50</div>
                    <div class="popular">∞</div>
                    <div class="popular">∞</div>
                </div>
                
                <div class="table-row">
                    <div>Download limit</div>
                    <div>20/day</div>
                    <div>500/month</div>
                    <div class="popular">∞</div>
                    <div class="popular">∞</div>
                </div>
                
                <div class="table-row">
                    <div>Storage space</div>
                    <div>1GB</div>
                    <div>10GB</div>
                    <div class="popular">100GB</div>
                    <div class="popular">∞</div>
                </div>
                
                <div class="table-row">
                    <div>IMEI repair tools</div>
                    <div><i class="icon-times"></i></div>
                    <div><i class="icon-times"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                </div>
                
                <div class="table-row">
                    <div>FRP removal</div>
                    <div><i class="icon-times"></i></div>
                    <div><i class="icon-times"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                </div>
                
                <div class="table-row">
                    <div>Priority support</div>
                    <div><i class="icon-times"></i></div>
                    <div><i class="icon-times"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                </div>
                
                <div class="table-row">
                    <div>Team management</div>
                    <div><i class="icon-times"></i></div>
                    <div><i class="icon-times"></i></div>
                    <div><i class="icon-times"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                </div>
                
                <div class="table-row">
                    <div>Custom integration</div>
                    <div><i class="icon-times"></i></div>
                    <div><i class="icon-times"></i></div>
                    <div><i class="icon-times"></i></div>
                    <div class="popular"><i class="icon-check"></i></div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- File Manager Section -->
    <section class="card file-manager">
        <div class="card-header">
            <h2><i class="icon-folder-open"></i> My Files</h2>
            <div class="header-actions">
                <input type="text" class="form-control search-input" placeholder="Search files...">
                <select class="form-control filter-select">
                    <option>All Categories</option>
                    <option>Firmware</option>
                    <option>Tools</option>
                    <option>Documentation</option>
                </select>
            </div>
        </div>
        
        <div class="card-body">
            <!-- File List View -->
            <div class="file-list-view" id="fileListView">
                
                <!-- File Item Template -->
                @foreach($files as $file)
                <div class="file-item">
                    <div class="file-icon">
                        @if($file->category === 'firmware')
                            <i class="icon-firmware"></i>
                        @elseif($file->category === 'tools')
                            <i class="icon-tools"></i>
                        @else
                            <i class="icon-document"></i>
                        @endif
                    </div>
                    
                    <div class="file-info">
                        <h4 class="file-name">{{ $file->original_name }}</h4>
                        <div class="file-meta">
                            <span class="file-size">{{ $file->file_size }}</span>
                            <span class="file-date">{{ $file->created_at->diffForHumans() }}</span>
                            @if($file->category)
                                <span class="file-category">{{ $file->category }}</span>
                            @endif
                        </div>
                        <div class="file-tags">
                            @foreach(explode(',', $file->tags ?? '') as $tag)
                                @if(trim($tag))
                                    <span class="tag">{{ trim($tag) }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="file-stats">
                        <span class="download-count">
                            <i class="icon-download"></i> {{ number_format($file->download_count) }}
                        </span>
                    </div>
                    
                    <div class="file-actions">
                        <button class="btn btn-icon" title="Download" onclick="downloadFile({{ $file->id }})">
                            <i class="icon-download"></i>
                        </button>
                        <button class="btn btn-icon" title="Share" onclick="shareFile({{ $file->id }})">
                            <i class="icon-share"></i>
                        </button>
                        <button class="btn btn-icon" title="Delete" onclick="deleteFile({{ $file->id }})">
                            <i class="icon-delete"></i>
                        </button>
                    </div>
                </div>
                @endforeach
                
            </div>
            
            <!-- Empty State -->
            <div class="empty-state" id="emptyState" style="display: none;">
                <i class="icon-folder-open"></i>
                <h3>No files found</h3>
                <p>Upload your first file to get started</p>
                <a href="#upload-section" class="btn btn-primary">Upload Files</a>
            </div>
            
        </div>
    </section>
    
</div>

@endsection

@section('scripts')
<script>
// Upload functionality
class FileUploader {
    constructor() {
        this.files = [];
        this.uploading = false;
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initDropZone();
        this.initBillingToggle();
    }
    
    bindEvents() {
        const uploadForm = document.getElementById('uploadForm');
        const clearBtn = document.getElementById('clearBtn');
        const fileInput = document.getElementById('fileInput');
        
        uploadForm.addEventListener('submit', (e) => this.handleSubmit(e));
        clearBtn.addEventListener('click', () => this.clearFiles());
        fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
    }
    
    initDropZone() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });
        
        dropZone.addEventListener('drop', (e) => this.handleDrop(e), false);
        dropZone.addEventListener('click', () => fileInput.click());
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        this.processFiles(files);
    }
    
    handleFileSelect(e) {
        const files = e.target.files;
        this.processFiles(files);
    }
    
    processFiles(files) {
        [...files].forEach(file => {
            if (this.validateFile(file)) {
                this.files.push(file);
                this.addFileToList(file);
            }
        });
    }
    
    validateFile(file) {
        const allowedTypes = [
            'application/zip', 'application/x-rar-compressed',
            'application/x-7z-compressed', 'application/x-tar',
            'application/gzip', 'application/x-binary',
            'application/x-image', 'application/x-iso9660-image'
        ];
        
        const maxSize = 2 * 1024 * 1024 * 1024; // 2GB
        
        if (!allowedTypes.includes(file.type) && 
            !/\.(zip|rar|7z|tar|gz|bin|img|iso)$/i.test(file.name)) {
            alert('File type not supported: ' + file.name);
            return false;
        }
        
        if (file.size > maxSize) {
            alert('File too large (max 2GB): ' + file.name);
            return false;
        }
        
        return true;
    }
    
    addFileToList(file) {
        const fileList = document.getElementById('fileList');
        const item = document.createElement('div');
        item.className = 'file-item-small';
        item.innerHTML = `
            <i class="icon-file"></i>
            <span class="filename">${file.name}</span>
            <span class="filesize">${this.formatFileSize(file.size)}</span>
            <button type="button" class="remove-file" onclick="this.parentElement.remove()">
                <i class="icon-close"></i>
            </button>
        `;
        fileList.appendChild(item);
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    clearFiles() {
        this.files = [];
        document.getElementById('fileList').innerHTML = '';
        document.getElementById('fileInput').value = '';
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (this.files.length === 0) {
            alert('Please select at least one file');
            return;
        }
        
        this.uploading = true;
        const progressBar = document.getElementById('uploadProgress');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        
        progressBar.style.display = 'block';
        
        const formData = new FormData();
        this.files.forEach(file => formData.append('files[]', file));
        
        // Add other form data
        const form = e.target;
        for (let [key, value] of new FormData(form)) {
            if (key !== 'files[]') {
                formData.append(key, value);
            }
        }
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    progressFill.style.width = percentCompleted + '%';
                    progressText.textContent = percentCompleted + '%';
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Files uploaded successfully!');
                this.clearFiles();
                progressBar.style.display = 'none';
                location.reload();
            } else {
                throw new Error(result.error || 'Upload failed');
            }
        } catch (error) {
            alert('Upload failed: ' + error.message);
            this.uploading = false;
        }
    }
    
    initBillingToggle() {
        const toggle = document.getElementById('billingToggle');
        const amounts = document.querySelectorAll('.amount');
        
        toggle.addEventListener('change', (e) => {
            amounts.forEach(amount => {
                const monthly = amount.dataset.monthly;
                const annual = amount.dataset.annual;
                amount.textContent = e.target.checked ? annual : monthly;
            });
        });
    }
}

// Plan selection
class PlanSelector {
    constructor() {
        this.init();
    }
    
    init() {
        document.querySelectorAll('.plan-card button').forEach(btn => {
            btn.addEventListener('click', (e) => this.selectPlan(e));
        });
    }
    
    selectPlan(e) {
        const btn = e.target;
        const plan = btn.dataset.plan;
        
        // Remove active class from all
        document.querySelectorAll('.plan-card').forEach(card => {
            card.classList.remove('active');
        });
        
        // Add active class to selected
        btn.closest('.plan-card').classList.add('active');
        
        // Handle checkout (simplified)
        alert(`Redirecting to ${plan} plan checkout...`);
        // window.location.href = `/checkout?plan=${plan}`;
    }
}

// File actions
function downloadFile(id) {
    window.location.href = `/api/firmware/download/${id}`;
}

function shareFile(id) {
    const url = window.location.origin + `/firmware/${id}`;
    if (navigator.share) {
        navigator.share({ url, title: 'Check out this firmware!' });
    } else {
        navigator.clipboard.writeText(url);
        alert('Link copied to clipboard!');
    }
}

function deleteFile(id) {
    if (confirm('Are you sure you want to delete this file?')) {
        fetch(`/api/files/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        }).then(response => response.json())
          .then(result => {
              if (result.success) {
                  location.reload();
              }
          });
    }
}

// Initialize
new FileUploader();
new PlanSelector();

// Animate counters
const counters = document.querySelectorAll('.stat-value[data-count]');
counters.forEach(counter => {
    const target = parseInt(counter.dataset.count);
    let current = 0;
    const increment = target / 50;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        counter.textContent = Math.floor(current).toLocaleString();
    }, 30);
});
</script>
<style>
/* Upload Form Styles */
.upload-section { background: linear-gradient(135deg, #151520 0%, #1a1a2e 100%); }
.hero-section { background: linear-gradient(135deg, #0a0a0f 0%, #151520 100%); }
.stats-bar { background: #1a1a2e; border-bottom: 1px solid #00ff88; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; padding: 40px 0; }
.stat-card { text-align: center; padding: 20px; border-radius: 12px; background: #151520; }
.stat-card.premium { border: 2px solid #ff00aa; }
.stat-icon { font-size: 2rem; margin-bottom: 10px; }
.stat-value { font-size: 2.5rem; font-weight: bold; color: #00ff88; }
.stat-label { color: #888; margin-top: 5px; }

/* Drop Zone */
.drop-zone { border: 2px dashed #00ff88; border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; }
.drop-zone:hover, .drop-zone.dragover { border-color: #ff00aa; background: rgba(255,0,170,0.1); }
.drop-zone input { display: none; }
.file-list { margin-top: 15px; }
.file-item-small { display: flex; align-items: center; padding: 10px; background: #1a1a2e; border-radius: 8px; margin: 5px 0; }
.file-item-small .filename { flex: 1; margin: 0 15px; }
.remove-file { background: none; border: none; color: #ff4444; cursor: pointer; }

/* Form Styles */
.radio-group, .checkbox-group { display: flex; flex-direction: column; gap: 10px; }
.radio-option, .checkbox-option { display: flex; align-items: center; padding: 15px; background: #1a1a2e; border-radius: 8px; cursor: pointer; }
.radio-option input, .checkbox-option input { display: none; }
.radio-custom, .checkbox-custom { width: 20px; height: 20px; border: 2px solid #00ff88; border-radius: 50%; margin-right: 15px; display: inline-block; }
.radio-option input:checked + .radio-custom { background: #00ff88; }
.checkbox-option input:checked + .checkbox-custom { background: #00ff88; }
.radio-label, .checkbox-label { display: flex; flex-direction: column; }
.radio-label span:last-child, .checkbox-label span:last-child { font-size: 0.85rem; color: #888; margin-top: 3px; }
.premium-badge { background: #ff00aa; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; margin-right: 10px; }
.premium-only { opacity: 0.7; }

/* Plans */
.plans-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 30px 0; }
.plan-card { border: 2px solid #333; border-radius: 12px; padding: 30px; text-align: center; background: #151520; transition: all 0.3s; position: relative; }
.plan-card.popular { border-color: #00ff88; }
.plan-card.popular .popular-badge { position: absolute; top: -10px}