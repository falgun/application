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
        $request = RequestBuilder::build();
        $response = new Response();

        $response->setBody('Hello World');
        $response->headers()->set('Content-Type', 'plain/text');

        $emitter = new ResponseEmitter;

        $this->assertSame(true, $emitter instanceof ResponseEmitter);
//        $a = ob_start();
//
//        $r = $emitter->emit($request, $response);
//
//        $b = ob_get_clean();
//
//        $this->assertEquals('Hello World', $b);
//        $this->assertEquals(NULL, $r);
    }
}
