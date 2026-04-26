# Changelog

All notable changes to the GSMSDK project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-26

### Added
- ✅ Initial release of GSMSDK framework v1.0.0
- 🏗️ Full MVC application structure
- 🔌 Dependency Injection Container (PSR-11 compatible)
- 🌐 HTTP Request/Response layer (PSR-7 inspired)
- 🗄️ Database Layer with Fluent Query Builder
  - Support for MySQL, PostgreSQL, SQLite
  - Parameterized queries (SQL injection safe)
  - Transaction support
- 🖥️ Desktop Application Support
  - Window management
  - Electron.js integration ready
  - Menu and tray support
- 📱 Mobile Application Support
  - Android & iOS configuration
  - Manifest generation (AndroidManifest.xml, Info.plist)
  - Permission management
  - Capability management
- 📡 ADB Integration (via adb-php)
  - Full ADB protocol implementation
  - Device management (list, connect, properties)
  - Shell command execution
  - APK install/uninstall
  - File push/pull operations
  - Screen capture
  - LogCat reading
  - Forward/Reverse proxy
  - Reboot, root, remount operations
- ⚡ Fastboot Integration (via fastboot-php)
  - Bootloader mode operations
  - Partition flash and erase
  - Fastboot variable inspection
  - Bootloader lock/unlock
  - Reboot options
- 🎮 CLI Console
  - Custom command registration
  - Built-in commands (version, status)
  - Task scheduling ready
- 📄 View Rendering System
  - XHTML templates with TheGridCN theme
  - Layout support
  - Partial/component support
- 🛡️ Middleware Support
  - Request/response pipeline
  - Built-in CORS support
  - CSRF protection
- 🎨 TheGridCN Theme
  - Modern dark design
  - Responsive layout
  - Tailwind CSS based

### Features
- Type-safe throughout (PHP 8.5+)
- Readonly classes for immutability
- Constructor property promotion
- Named arguments support
- Union types
- Match expressions
- Attributes for metadata
- Generic support
- Variadic arguments
- Null safety

### Architecture
- Clean separation of concerns
- SOLID principles
- PSR standards compliance
- Event-driven architecture
- Macro system for extensions
- Configurable via dot notation
- Environment-aware configuration

### Examples
- Desktop application example
- Mobile app configuration example
- API server example
- CLI console example
- Integrated ADB+Fastboot device operations

### Documentation
- Comprehensive README (300+ lines)
- API reference
- Architecture guide
- Usage examples
- Quick start guide

### Testing
- PHPUnit test suite
- Unit tests for core components
- Mock support for testing
- Coverage reporting ready

## [Unreleased]

### Planned
- WebSocket support
- GraphQL server implementation
- Queue system
- Cache layer (Redis, Memcached)
- Authentication & Authorization
- ORM with migrations
- Event dispatcher
- Logging system (Monolog integration)
- Scheduler (Cron-like)
- Package manager integration
- Plugin system
- Multi-language support (i18n)
- Theme system
- Admin panel generator

## [Future]

### Version 2.0
- Full ORM with relationships
- Real-time with WebSockets
- Microservices support
- Kubernetes deployment templates
- Docker integration
- CI/CD pipelines
- Performance monitoring
- APM integration

---

**GSMSDK** - Full-Stack PHP Framework for Desktop & Mobile Applications

MIT License - Copyright (c) 2026 GSMSDK Team

## [Unreleased] - 2026-04-26

### Added
- 🎨 GSM Templating Engine (Blade-inspired)
  - Template compilation with caching
  - Directives: @if, @foreach, @for, @while, @include, @extends, @section
  - Echo statements: {{ }} (escaped) and {!! !!} (raw)
  - Control structures: @unless, @auth, @guest, @can, @isset, @empty
  - CSRF protection: @csrf, Method spoofing: @method
  - PHP blocks: @php, Dump helpers: @dump, @dd
  - Error handling: @error, Selected/checked helpers
  - Component support: @component / @endcomponent
  - Layout inheritance with @yield and @parent

### Modified
- Updated View class to use GSM engine
- Front controller updated to demonstrate GSM templates
- Enhanced documentation with GSM syntax guide

### Examples
- main.gsm.php - Main layout template
- home.gsm.php - Home page with GSM features
- example.gsm.php - GSM syntax demonstrations
- GsmDemoController.php - Demo controller for templates
