<?php
declare(strict_types=1);

namespace Falgun\Application;

final class Config
{
    private array $configurations;

    private final function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * @param string $directory
     * @param array<string, mixed> $predefined
     * @return \self
     * @throws \RuntimeException
     * @psalm-suppress UnresolvableInclude
     */
    public static function fromFileDir(string $directory, array $predefined = []): self
    {
        $dirIterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);

        $configurations = $predefined;

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {

            if ($file->isDir()) {
                continue;
            }

            $configs = require $file->getRealPath();

            $configurations = \array_merge($configurations, $configs);
        }

        if (isset($file) === false) {
            throw new \RuntimeException('No config file found');
        }

        return new static($configurations);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get(string $key)
    {
        if (\array_key_exists($key, $this->configurations)) {
            return $this->configurations[$key];
        }

        throw new \InvalidArgumentException('Config for "' . $key . '" not found!');
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getIfAvailable(string $key, $default)
    {
        if (\array_key_exists($key, $this->configurations)) {
            return $this->configurations[$key];
        }

        return $default;
    }
}
