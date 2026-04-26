<?php

declare(strict_types=1);

namespace GSMSDK\Mobile;

use GSMSDK\Core\Application as CoreApplication;
use GSMSDK\Traits\Configurable;

/**
 * Mobile Application Configuration
 *
 * Handles configuration for Android/iOS mobile applications
 */
class App
{
    use Configurable;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'name' => 'GSMSDK App',
            'identifier' => 'io.gsmsdk.app',
            'version' => '1.0.0',
            'build' => '1',
            'platforms' => ['android', 'ios'],
            'permissions' => [],
            'capabilities' => [],
        ], $config);
    }

    /**
     * Get application name
     */
    public function getName(): string
    {
        return $this->config['name'];
    }

    /**
     * Set application name
     */
    public function setName(string $name): self
    {
        $this->config['name'] = $name;
        return $this;
    }

    /**
     * Get bundle identifier
     */
    public function getIdentifier(): string
    {
        return $this->config['identifier'];
    }

    /**
     * Set bundle identifier
     */
    public function setIdentifier(string $identifier): self
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)+$/', $identifier)) {
            throw new \InvalidArgumentException("Invalid bundle identifier: {$identifier}");
        }
        $this->config['identifier'] = $identifier;
        return $this;
    }

    /**
     * Get version
     */
    public function getVersion(): string
    {
        return $this->config['version'];
    }

    /**
     * Set version
     */
    public function setVersion(string $version): self
    {
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new \InvalidArgumentException("Invalid version format: {$version}. Use semantic versioning (e.g., 1.0.0)");
        }
        $this->config['version'] = $version;
        return $this;
    }

    /**
     * Get build number
     */
    public function getBuild(): string
    {
        return $this->config['build'];
    }

    /**
     * Set build number
     */
    public function setBuild(string $build): self
    {
        $this->config['build'] = $build;
        return $this;
    }

    /**
     * Add platform
     */
    public function addPlatform(string $platform): self
    {
        $platform = strtolower($platform);
        if (!in_array($platform, ['android', 'ios'])) {
            throw new \InvalidArgumentException("Unsupported platform: {$platform}");
        }

        if (!in_array($platform, $this->config['platforms'])) {
            $this->config['platforms'][] = $platform;
        }

        return $this;
    }

    /**
     * Get platforms
     *
     * @return array<string>
     */
    public function getPlatforms(): array
    {
        return $this->config['platforms'];
    }

    /**
     * Add permission
     */
    public function addPermission(string $permission): self
    {
        if (!in_array($permission, $this->config['permissions'])) {
            $this->config['permissions'][] = $permission;
        }
        return $this;
    }

    /**
     * Get permissions
     *
     * @return array<string>
     */
    public function getPermissions(): array
    {
        return $this->config['permissions'];
    }

    /**
     * Add capability
     */
    public function addCapability(string $capability): self
    {
        if (!in_array($capability, $this->config['capabilities'])) {
            $this->config['capabilities'][] = $capability;
        }
        return $this;
    }

    /**
     * Get capabilities
     *
     * @return array<string>
     */
    public function getCapabilities(): array
    {
        return $this->config['capabilities'];
    }

    /**
     * Generate AndroidManifest.xml snippet
     */
    public function generateAndroidManifest(): string
    {
        $manifest = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $manifest .= '<manifest xmlns:android="http://schemas.android.com/apk/res/android"' . "\n";
        $manifest .= sprintf('    package="%s">' . "\n", $this->config['identifier']);
        $manifest .= "\n";

        foreach ($this->config['permissions'] as $permission) {
            $manifest .= sprintf('    <uses-permission android:name="%s" />' . "\n", $permission);
        }

        $manifest .= "\n";
        $manifest .= '    <application' . "\n";
        $manifest .= '        android:label="' . $this->config['name'] . '"' . "\n";
        $manifest .= '        android:versionName="' . $this->config['version'] . '"' . "\n";
        $manifest .= '        android:versionCode="' . $this->config['build'] . '">' . "\n";
        $manifest .= '    </application>' . "\n";
        $manifest .= '</manifest>' . "\n";

        return $manifest;
    }

    /**
     * Generate Info.plist snippet
     */
    public function generateInfoPlist(): string
    {
        $plist = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $plist .= '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">' . "\n";
        $plist .= '<plist version="1.0">' . "\n";
        $plist .= '<dict>' . "\n";
        $plist .= sprintf('    <key>CFBundleIdentifier</key><string>%s</string>' . "\n", $this->config['identifier']);
        $plist .= sprintf('    <key>CFBundleName</key><string>%s</string>' . "\n", $this->config['name']);
        $plist .= sprintf('    <key>CFBundleShortVersionString</key><string>%s</string>' . "\n", $this->config['version']);
        $plist .= sprintf('    <key>CFBundleVersion</key><string>%s</string>' . "\n", $this->config['build']);
        $plist .= '</dict>' . "\n";
        $plist .= '</plist>' . "\n";

        return $plist;
    }
}
