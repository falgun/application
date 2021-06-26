<?php
declare(strict_types=1);

namespace Falgun\Application\Tests;

use Falgun\Routing\Router;
use Falgun\Fountain\Fountain;
use Falgun\Application\Config;
use PHPUnit\Framework\TestCase;
use Falgun\Reporter\ProdReporter;
use Falgun\Application\Application;

final class ApplicationTest extends TestCase
{

    public function testApplication()
    {
        $predefined = ['ROOT_DIR' => __DIR__];
        $config = Config::fromFileDir(__DIR__ . '/Stubs/Config', $predefined);
        $container = new Fountain();
        $middlewareGroups = [];
        $reporter = new ProdReporter();
        $request = RequestBuilder::build();
        $router = new Router($request->uri()->getFullDocumentRootUrl());
        $router->any('/')->closure(function() {
            echo 'Hello World';
        });

        $application = new Application($config, $container, $router, $middlewareGroups, $reporter);

        $this->expectOutputString('Hello World');
        $application->run($request);
    }

    public function testApplicationWithMiddleware()
    {
        $predefined = ['ROOT_DIR' => __DIR__];
        $config = Config::fromFileDir(__DIR__ . '/Stubs/Config', $predefined);
        $container = new Fountain();
        $middlewareGroups = [];
        $reporter = new ProdReporter();
        $request = RequestBuilder::build();
        $router = new Router($request->uri()->getFullDocumentRootUrl());
        $router->any('/')
            ->closure(function() {
                echo 'Hello World With Middlewares';
            })
            ->middleware([Stubs\Middlewares\FakeMiddleware::class]);

        $application = new Application($config, $container, $router, $middlewareGroups, $reporter);

        $this->expectOutputString('Hello World With Middlewares');
        $application->run($request);

        $this->assertSame(1, $request->attributes()->get('layers'));
        $this->assertSame(['FakeMiddleware'], $request->attributes()->get('middlewares'));
    }
}
