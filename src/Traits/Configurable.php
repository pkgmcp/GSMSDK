<?php

declare(strict_types=1);

namespace GSMSDK\Traits;

trait Configurable
{
    protected array $config = [];

    public function getConfig(string $key, mixed $default = null): mixed
    {
        $value = $this->config;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    public function setConfig(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $target = &$this->config;
        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }
        $target[array_shift($keys)] = $value;
    }

    public function hasConfig(string $key): bool
    {
        $value = $this->config;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            $value = $value[$segment];
        }
        return true;
    }

    public function getAllConfig(): array
    {
        return $this->config;
    }
}
