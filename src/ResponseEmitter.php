<?php
declare(strict_types=1);

namespace Falgun\Application;

use Falgun\Http\RequestInterface;
use Falgun\Http\ResponseInterface;
use Falgun\Http\Parameters\Headers;
use Falgun\Template\TemplateInterface;

final class ResponseEmitter
{
    public function emit(RequestInterface $request, ResponseInterface $response): void
    {
        $this->mustBeCleanSlate();

        $this->sendStatusCode($response);
        $this->sendHeaders($response->headers());

        if ($request->getMethod() === 'HEAD') {
            exit;
        }

        if ($response instanceof TemplateInterface) {
            $response->render();
        } else {
            echo $response->getBody();
        }

        $this->terminate();
    }

    private function terminate(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        exit;
    }

    private function mustBeCleanSlate(): void
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

    private function sendHeaders(Headers $headers): void
    {
        foreach ($headers->all() as $name => $value) {
            \header($name . ': ' . $value);
        }
    }

    private function sendStatusCode(ResponseInterface $response): void
    {
        \http_response_code($response->getStatusCode());
    }
}
