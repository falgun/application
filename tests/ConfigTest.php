<?php
declare(strict_types=1);

namespace Falgun\Application\Tests;

use Falgun\Application\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{

    public function testConfigArray()
    {
        $config = Config::fromFileDir(__DIR__ . '/Stubs/Config');

        $this->assertSame(false, $config->get('debug'));
        $this->assertSame(true, $config->get('nested'));
    }

    public function testPredefinedConfig()
    {
        $config = Config::fromFileDir(__DIR__ . '/Stubs/Config', ['predefined' => 'value']);

        $this->assertSame('value', $config->get('predefined'));
    }

    public function testInvalidDir()
    {
        $this->expectException(\UnexpectedValueException::class);

        Config::fromFileDir(__DIR__ . '/invalid');
    }

    public function testEmptyDir()
    {
        $this->expectException(\RuntimeException::class);

        Config::fromFileDir(__DIR__ . '/Stubs/EmptyConfig');
    }

    public function testInvalidArgument()
    {
        $config = Config::fromFileDir(__DIR__ . '/Stubs/Config');

        $this->expectException(\InvalidArgumentException::class);

        $config->get('invalid');
    }
    
    public function testSafeGetArgument()
    {
        $config = Config::fromFileDir(__DIR__ . '/Stubs/Config');

        $this->assertSame(false, $config->getIfAvailable('debug', null));
        $this->assertSame(null, $config->getIfAvailable('invalid', null));
        $this->assertSame(true, $config->getIfAvailable('invalid', true));
    }
}
