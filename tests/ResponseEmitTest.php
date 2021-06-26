<?php
declare(strict_types=1);

namespace Falgun\Application\Tests;

use Falgun\Http\Response;
use Falgun\Application\ResponseEmitter;
use PHPUnit\Framework\TestCase;

class ResponseEmitTest extends TestCase
{

    /**
     * @runInSeparateProcess
     */
    public function testResponseEmit()
    {
        $request = RequestBuilder::build();
        $response = new Response();

        $response->setBody('Hello World');
        $response->headers()->set('Content-Type', 'plain/text');

        $emitter = new ResponseEmitter();

        $this->assertSame(true, $emitter instanceof ResponseEmitter);

        ob_start();
        $emitter->emit($request, $response);
        $output = ob_get_clean();

        $this->assertEquals('Hello World', $output);
    }

    public function testHeadersAlreadySent()
    {
        $request = RequestBuilder::build();
        $response = new Response();

        $emitter = new ResponseEmitter();

        $this->expectException(\RuntimeException::class);
        $emitter->emit($request, $response);
    }
    
    /**
     * @runInSeparateProcess
     */
//    public function testHeadMethod()
//    {
//        $request = RequestBuilder::build();
//
//        $response = new Response('Hello');
//        $response->headers()->set('Content-Type', 'text/html');
//
//        $emitter = new ResponseEmitter();
//
//        $this->expectOutputString('');
//        $emitter->emit($request, $response);
//
//        $this->assertTrue(\headers_sent());
//    }
}
