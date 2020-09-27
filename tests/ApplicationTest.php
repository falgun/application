<?php
declare(strict_types=1);

namespace Falgun\Application\Tests;

use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{

    public function testApplication()
    {
        $config = new \Falgun\Application\Config([]);
        $container = new \Falgun\Fountain\Fountain(new \Falgun\Fountain\SharedServices());
        $router = new \Falgun\Routing\Router('');
        $middlewareGroups = [];
        $reporter = new \Falgun\Reporter\ProdReporter();
        $request = new \Falgun\Http\Request($uri, $queryDatas, $postDatas, $attributes, $headers, $cookies, $files, $serverDatas);

        $application = new \Falgun\Application\Application($config, $container, $router, $middlewareGroups, $reporter);
        $application->run($request);
    }
}
