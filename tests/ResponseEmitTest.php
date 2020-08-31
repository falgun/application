<?php
declare(strict_types=1);

namespace Falgun\Application\Tests;

use Falgun\Http\Request;
use Falgun\Http\Response;
use Falgun\Application\ResponseEmitter;
use PHPUnit\Framework\TestCase;

class ResponseEmitTest extends TestCase
{

    public function testResponseEmit()
    {
        $_SERVER = [
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '8080',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTP_HOST' => 'localhost:8080',
            'REQUEST_URI' => '/skeleton/public/',
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING' => 'test=true',
            'REQUEST_SCHEME' => 'http',
        ];

        $request = Request::createFromGlobals();
        $response = new Response();

        $response->setBody('Hello World');
        $response->headers()->set('Content-Type', 'plain/text');

        $emitter = new ResponseEmitter;

        $a = ob_start();

        $r = $emitter->emit($request, $response);

        $b = ob_get_clean();

        $this->assertEquals('Hello World', $b);
        $this->assertEquals(NULL, $r);
    }
}
