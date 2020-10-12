<?php
declare(strict_types=1);

namespace Falgun\Application\Tests\Stubs\Middlewares;

use Falgun\Http\RequestInterface;
use Falgun\Midlayer\LayersInterface;
use Falgun\Midlayer\MiddlewareInterface;

final class FakeMiddleware implements MiddlewareInterface
{

    public function handle(RequestInterface $request, LayersInterface $layers)
    {
        $this->incrementLayerCount($request);
        $this->registerMiddlewareName($request);

        return $layers->next($request);
    }

    private function incrementLayerCount(RequestInterface $request)
    {
        $layerNo = $request->attributes()->get('layers', 0);

        $request->attributes()->set('layers', ++$layerNo);
    }

    private function registerMiddlewareName(RequestInterface $request)
    {
        $middlewares = $request->attributes()->get('middlewares', []);
        $middlewares[] = 'FakeMiddleware';

        $request->attributes()->set('middlewares', $middlewares);
    }
}
