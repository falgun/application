<?php
declare(strict_types=1);

namespace {
    define('ROOT_DIR', dirname(__DIR__));
}

namespace Falgun\Application\Tests {

    use Falgun\Routing\Router;
    use Falgun\Fountain\Fountain;
    use Falgun\Application\Config;
    use PHPUnit\Framework\TestCase;
    use Falgun\Reporter\ProdReporter;
    use Falgun\Application\Application;
    use Falgun\Fountain\SharedServices;

    final class ApplicationTest extends TestCase
    {

        public function testApplication()
        {
            $config = Config::fromFileDir(__DIR__ . '/Stubs/Config');
            $container = new Fountain(new SharedServices());
            $middlewareGroups = [];
            $reporter = new ProdReporter();
            $request = RequestBuilder::build();
            $router = new Router($request->uri()->getFullDocumentRootUrl());
            $router->any('/')->closure(function() {
                return true;
            });

            $application = new Application($config, $container, $router, $middlewareGroups, $reporter);
            $application->run($request);

            $this->assertTrue(true);
        }

        public function testApplicationWithMiddleware()
        {
            $config = Config::fromFileDir(__DIR__ . '/Stubs/Config');
            $container = new Fountain(new SharedServices());
            $middlewareGroups = [];
            $reporter = new ProdReporter();
            $request = RequestBuilder::build();
            $router = new Router($request->uri()->getFullDocumentRootUrl());
            $router->any('/')->closure(function() {
                return true;
            })->middleware([Stubs\Middlewares\FakeMiddleware::class]);

            $application = new Application($config, $container, $router, $middlewareGroups, $reporter);
            $application->run($request);

            $this->assertSame(1, $request->attributes()->get('layers'));
            $this->assertSame(['FakeMiddleware'], $request->attributes()->get('middlewares'));
        }
    }

}