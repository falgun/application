<?php
declare(strict_types=1);

namespace Falgun\Application;

class Config
{

    protected array $configurations;

    public function __construct(string $configDir)
    {
        $this->loadConfigFromFile($configDir);
    }

    protected function loadConfigFromFile(string $directory)
    {
        $dirIterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);

        $this->configurations = [];

        foreach ($iterator as $file) {

            if ($file->isDir()) {
                continue;
            }

            $configs = require $file->getRealPath();

            $this->configurations = \array_merge($this->configurations, $configs);
        }
    }

    public function get(string $key)
    {
        if (\array_key_exists($key, $this->configurations)) {
            return $this->configurations[$key];
        }

        throw new \Exception('Config for "' . $key . '" not found!');
    }

    public function getIfAvailable(string $key, $default)
    {
        if (\array_key_exists($key, $this->configurations)) {
            return $this->configurations[$key];
        }

        return $default;
    }
}
