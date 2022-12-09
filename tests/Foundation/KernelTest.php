<?php

namespace Tests\Foundation;

use Gravatalonga\KingFoundation\Kernel;
use Gravatalonga\KingFoundation\SlimServiceProvider;
use Gravatalonga\KingFoundation\Testing\TraitRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

/**
 * @covers \Gravatalonga\KingFoundation\Kernel
 */
class KernelTest extends TestCase
{
    use TraitRequest;

    public Kernel $http;

    public function setUp(): void
    {
        $this->http = new Kernel(null, [
            new SlimServiceProvider(),
        ]);
    }

    /**
     * @test
     */
    public function can_handle_request()
    {
        $this->http->get('/', function (Request $rq, Response $rs) {
            $rs->getBody()->write('hello world');

            return $rs;
        });

        $response = $this->http->handle($this->createRequest('GET', '/'));
        $body = (string)$response->getBody();

        $this->assertNotEmpty($body);
        $this->assertEquals('hello world', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @dataProvider dataProviderCanHandleEveryMethod
     */
    public function can_handle_every_method(string $method, string $uri)
    {
        $this->http->{$method}($uri, function (Request $rq, Response $rs) {
            $rs->getBody()->write($rq->getMethod().'-'.$rq->getUri()->getPath());

            return $rs;
        });

        $response = $this->http->handle($this->createRequest(strtoupper($method), $uri));
        $body = $response->getBody();

        $this->assertNotEmpty($body);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(strtoupper($method).'-'.$uri, (string)$body);
    }

    /**
     * @test
     * @dataProvider dataProviderHandleAnyMethod
     */
    public function handle_any_method(string $method)
    {
        $this->http->any('/any-method', function (Request $rq, Response $rs) {
            $rs->getBody()->write($rq->getMethod().'-'.$rq->getUri()->getPath());

            return $rs;
        });

        $response = $this->http->handle($this->createRequest(strtoupper($method), '/any-method'));
        $body = $response->getBody();

        $this->assertNotEmpty($response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(strtoupper($method).'-/any-method', (string)$body);
    }

    /**
     * @test
     */
    public function can_handle_map_method()
    {
        $this->http->map(['GET', 'POST'], '/get-or-post', function (Request $rq, Response $rs) {
            $rs->getBody()->write($rq->getMethod().'-'.$rq->getUri()->getPath());

            return $rs;
        });

        $responseGet = $this->http->handle($this->createRequest('GET', '/get-or-post'));
        $bodyGet = $responseGet->getBody();

        $responsePost = $this->http->handle($this->createRequest('POST', '/get-or-post'));
        $bodyPost = $responsePost->getBody();

        $this->assertNotEmpty($bodyGet);
        $this->assertEquals(200, $responseGet->getStatusCode());
        $this->assertEquals('GET-/get-or-post', (string)$bodyGet);

        $this->assertNotEmpty($bodyPost);
        $this->assertEquals(200, $responsePost->getStatusCode());
        $this->assertEquals('POST-/get-or-post', (string)$bodyPost);
    }

    /**
     * @test
     */
    public function it_can_handle_group()
    {
        $this->http->group('/my-group', function (RouteCollectorProxy $group) {
            $group->get('/hello', function (Request $rq, Response $rs) {
                $rs->getBody()->write("world!");

                return $rs;
            });
        });

        $response = $this->http->handle($this->createRequest('GET', '/my-group/hello'));
        $body = $response->getBody();

        $this->assertNotEmpty($body);
        $this->assertEquals('world!', (string)$body);
    }

    /**
     * @test
     */
    public function can_add_middleware_on_method()
    {
        $this->http->get('/hello', function (Request $rq, Response $rs) {
            $rs->getBody()->write("world!");

            return $rs;
        })->add(function (Request $request, RequestHandlerInterface $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('AFTER');

            return $response;
        });

        $response = $this->http->handle($this->createRequest('GET', '/hello'));
        $body = $response->getBody();

        $this->assertNotEmpty($body);
        $this->assertEquals('world!AFTER', (string)$body);
    }

    /**
     * @test
     */
    public function can_add_middleware()
    {
        $this->http->add(function (Request $request, RequestHandlerInterface $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('AFTER');

            return $response;
        });

        $this->http->get('/hello', function (Request $rq, Response $rs) {
            $rs->getBody()->write("world!");

            return $rs;
        });

        $response = $this->http->handle($this->createRequest('GET', '/hello'));
        $body = $response->getBody();

        $this->assertNotEmpty($body);
        $this->assertEquals('world!AFTER', (string)$body);
    }
    
    /**
     * @test
     * @dataProvider dataProviderAddParseBodyMiddleware
     */
    public function add_parse_body_middleware(string $contentType, string $payload, string $expected)
    {
        $this->http->post('/', function (Request $request, Response $response) {
            $response->getBody()->write(gettype($request->getParsedBody()));

            return $response;
        });

        $response = $this->http->handle($this->createRequest('POST', '/', $payload, [
            'Content-Type' => $contentType,
        ]));

        $body = (string)$response->getBody();

        $this->assertEquals($expected, $body);
    }

    public function dataProviderAddParseBodyMiddleware()
    {
        return [
            ['application/json', '{"type":"hello"}', 'array'],
            ['application/x-www-form-urlencoded', 'type=hello', 'array'],
            ['application/xml', '<type>hello</type>', 'object'],
            ['text/xml', '<type>hello</type>', 'object'],
        ];
    }

    public function dataProviderHandleAnyMethod()
    {
        return [
            ['get'],
            ['post'],
            ['put'],
            ['patch'],
            ['delete'],
            ['options'],
        ];
    }

    public function dataProviderCanHandleEveryMethod()
    {
        return [
            'get' => ['get', '/get'],
            'post' => ['post', '/post'],
            'put' => ['put', '/put'],
            'patch' => ['patch', '/patch'],
            'delete' => ['delete', '/delete'],
            'options' => ['options', '/options'],
        ];
    }
}
