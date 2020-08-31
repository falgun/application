<?php
declare(strict_types=1);

namespace Falgun\Application;

use Falgun\Http\Request;
use Falgun\Http\Response;
use Falgun\Http\Parameters\Headers;
use Falgun\Template\TemplateInterface;

class ResponseEmitter
{

    public function emit(Request $request, Response $response): void
    {
        $this->mustBeCleanSlate();

        $this->sendStatusCode($response);
        $this->sendHeaders($response->headers());

        if ($request->isHeadMethod()) {
            exit;
        }

        if ($response instanceof TemplateInterface) {
            $response->render();
        } else {
            echo $response->getBody();
        }

        exit;
    }

    protected function mustBeCleanSlate(): void
    {

        $file = null;
        $line = null;

        if (\headers_sent($file, $line)) {
            throw new \RuntimeException(<<<TEXT
                Headers were already sent in {$file} on line {$line}.
                The response could not be emitted!
                TEXT);
        }
    }

    protected function sendHeaders(Headers $headers): void
    {
        foreach ($headers->all() as $name => $value) {
            \header($name . ': ' . $value);
        }
    }

    protected function sendStatusCode(Response $response): void
    {
        \http_response_code($response->getStatusCode());
    }
}
