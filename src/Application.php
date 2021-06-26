<?php
declare(strict_types=1);

namespace Falgun\Application;

use Closure;
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

final class Application
{

    private Config $config;
    private RouterInterface $router;
    private ContainerInterface $container;
    private array $middlewareGroups;
    private ReporterInterface $reporter;

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

    public function run(RequestInterface $request): void
    {
        $requestContext = RequestContext::fromUriParts(
            $request->getMethod(),
            $request->uri()->getScheme(),
            $request->uri()->getHost(),
            $request->uri()->getPort(),
            $request->uri()->getPath(),
        );

        /* @var $route RouteInterface */
        $route = $this->router->dispatch($requestContext);

        $response = $this->runThroughMiddleWare($route, $request);

        $this->reporter->setCurrentController($route->getController());
        $this->reporter->setCurrentMethod($route->getMethod());

        $appDir = $this->config->get('ROOT_DIR') . '/' . $this->config->getIfAvailable('APP_DIR', 'src');

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

    /**
     * @param RouteInterface $route
     * @param RequestInterface $request
     * @return mixed
     */
    private function runThroughMiddleWare(RouteInterface $route, RequestInterface $request)
    {

        $middleWares = $this->prepareMiddlewareStack($route);

        $target = $this->targetForMiddlewares($route);

        $midlayer = new Midlayer($middleWares);

        $midlayer->setResolver($this->resolverForMiddlewares());

        return $midlayer->run($request, $target);
    }

    /**
     * @return Closure(class-string<MiddlewareInterface>): MiddlewareInterface
     */
    private function resolverForMiddlewares(): Closure
    {
        return function (string $className): MiddlewareInterface {
            return $this->container->get($className);
        };
    }

    /**
     * @param RouteInterface $route
     * @return Closure(): mixed
     */
    private function targetForMiddlewares(RouteInterface $route): Closure
    {
        return function() use($route) {
            return $this->callController($route);
        };
    }

    private function prepareMiddlewareStack(RouteInterface $route): array
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

    /**
     * @param RouteInterface $route
     * @return mixed
     */
    private function callController(RouteInterface $route)
    {
        $controller = $route->getController();
        $closure = $route->getClosure();

        if (!empty($controller)) {
            // controller-action route
            $object = $this->container->get($controller);
            $parameters = \array_values($route->getParameters());

            return $object->{$route->getMethod()}(...$parameters);
        } elseif ($closure instanceof \Closure) {
            $parameters = \array_values($route->getParameters());

            return $closure(...$parameters);
        }
    }
}
