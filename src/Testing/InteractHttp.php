<?php declare(strict_types=1);

namespace Gravatalonga\KingFoundation\Testing;

use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;

trait InteractHttp
{
    public function createRequest(string $method, string $uri, ?string $payload = null, array $headers = []): Request
    {
        $stream = new StreamFactory();
        $stream = $stream->createStream('');

        if (! empty($payload)) {
            $stream = new StreamFactory();
            $handle = fopen('php://temp', 'w+');
            $stream = $stream->createStreamFromResource($handle);
            $stream->write($payload);
        }

        $uri = new Uri('', '', 80, $uri);
        $h = new Headers();
        foreach ($headers as $key => $value) {
            $h->addHeader($key, $value);
        }

        return new Request($method, $uri, $h, [], [], $stream);
    }
}
