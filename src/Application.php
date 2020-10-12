<?php
declare(strict_types=1);

namespace Falgun\Application;

use Falgun\Http\Request;
use Falgun\Midlayer\Midlayer;
use Falgun\Http\RequestInterface;
use Falgun\Http\ResponseInterface;
use Falgun\Routing\RequestContext;
use Falgun\Routing\RouteInterface;
use Falgun\Routing\RouterInterface;
use Falgun\Reporter\ReporterInterface;
use Falgun\Template\TemplateInterface;
use Falgun\Fountain\ContainerInterface;
use Falgun\Midlayer\MiddlewareInterface;

class Application
{

    protected Config $config;
    protected RouterInterface $router;
    protected ContainerInterface $container;
    protected array $middlewareGroups;
    protected ReporterInterface $reporter;

    public function __construct(
        Config $config,
        ContainerInterface $container,
        RouterInterface $router,
        array $middlewareGroups,
        ReporterInterface $reporter
    )
    {
        $this->config = $config;
        $this->container = $container;
        $this->router = $router;
        $this->middlewareGroups = $middlewareGroups;
        $this->reporter = $reporter;
    }

    public function run(Request $request): void
    {
        $requestContext = new RequestContext(
            $request->getMethod(),
            $request->uri()->getScheme(),
            $request->uri()->getHost(),
            $request->uri()->getPath(),
        );
        
        /* @var $route RouteInterface */
        $route = $this->router->dispatch($requestContext);

        $response = $this->runThroughMiddleWare($route, $request);

        $this->reporter->setCurrentController($route->getController());
        $this->reporter->setCurrentMethod($route->getMethod());

        $appDir = ROOT_DIR . '/' . $this->config->getIfAvailable('APP_DIR', 'src');

        if ($response instanceof TemplateInterface) {
            $response->setViewDirFromControllerPath($route->getController(), $appDir . '/Views');

            $this->reporter->setCurrentTemplate(get_class($response));
            $this->reporter->setCurrentView($response->getViewAbsolutePath());
        }

        if ($response instanceof ResponseInterface) {
            $responseEmitter = new ResponseEmitter();
            $responseEmitter->emit($request, $response);
        }
    }

    protected function runThroughMiddleWare(RouteInterface $route, RequestInterface $request)
    {

        $middleWares = $this->prepareMiddlewareStack($route);

        $target = function() use($route) {
            return $this->callController($route);
        };

        $midlayer = new Midlayer($middleWares);
        $container = $this->container;
        $midlayer->setResolver(function (string $className) use($container): MiddlewareInterface {
            return $container->get($className);
        });

        return $midlayer->run($request, $target);
    }

    protected function prepareMiddlewareStack(RouteInterface $route): array
    {
        if (empty($route->getMiddlewares())) {
            return $this->middlewareGroups['web'] ?? [];
        }

        $middleWares = [];
        $groupAssigned = false;

        foreach ($route->getMiddlewares() as $middleware) {
            if (isset($this->middlewareGroups[$middleware])) {
                $middleWares = \array_merge($middleWares, $this->middlewareGroups[$middleware]);
                $groupAssigned = true;
                continue;
            }

            $middleWares[] = $middleware;
        }

        if ($groupAssigned === false) {
            $middleWares = \array_merge(($this->middlewareGroups['web'] ?? []), $middleWares);
        }

        return \array_filter($middleWares);
    }

    protected function callController(RouteInterface $route)
    {
        if (!empty($route->getController())) {
            // controller-action route
            $object = $this->container->get($route->getController());
            $parameters = \array_values($route->getParameters());

            return $object->{$route->getMethod()}(...$parameters);
        } elseif ($route->getClosure() instanceof \Closure) {
            $parameters = \array_values($route->getParameters());

            return $route->getClosure()(...$parameters);
        }
    }
}
