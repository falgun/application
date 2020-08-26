<?php
declare(strict_types=1);

namespace Falgun\Application;

class Config
{

    protected array $configurations;

    private final function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    public static function fromFileDir(string $directory): self
    {
        $dirIterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);

        $configurations = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {

            if ($file->isDir()) {
                continue;
            }

            $configs = require $file->getRealPath();

            $configurations = \array_merge($configurations, $configs);
        }

        if (empty($configurations)) {
            throw new \Exception('No config file found');
        }

        return new static($configurations);
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
