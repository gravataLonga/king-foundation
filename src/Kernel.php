<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Gravatalonga\Framework\App;
use Gravatalonga\Framework\ValueObject\Path;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollector;

final class Kernel
{
    private RouteCollector $routeCollector;

    private App $app;

    private ResponseFactoryInterface $responseFactory;

    private CallableResolver $callableResolver;

    private SlimApp $slimApp;

    public function __construct(?Path $path = null, array $providers = [])
    {
        $this->bootApplication($path, $providers);

        $this->slimApp = $this->createSlimApplication();
    }

    public function get(string $uri, $callable): RouteInterface
    {
        return $this->map(['GET'], $uri, $callable);
    }

    public function post(string $uri, $callable): RouteInterface
    {
        return $this->map(['POST'], $uri, $callable);
    }

    public function put(string $uri, $callable): RouteInterface
    {
        return $this->map(['PUT'], $uri, $callable);
    }

    public function patch(string $uri, $callable): RouteInterface
    {
        return $this->map(['PATCH'], $uri, $callable);
    }

    public function delete(string $uri, $callable): RouteInterface
    {
        return $this->map(['DELETE'], $uri, $callable);
    }

    public function options(string $uri, $callable): RouteInterface
    {
        return $this->map(['OPTIONS'], $uri, $callable);
    }

    public function any(string $uri, $callable): RouteInterface
    {
        return $this->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $uri, $callable);
    }

    public function map(array $methods, string $uri, $callable): RouteInterface
    {
        return $this->routeCollector->map($methods, $uri, $callable);
    }

    public function group(string $pattern, $callable): RouteGroupInterface
    {
        return $this->routeCollector->group($pattern, $callable);
    }

    public function add($middleware): self
    {
        $this->slimApp->add($middleware);

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->slimApp->handle($request);
    }

    public function run(?ServerRequestInterface $request = null): void
    {
        $this->slimApp->run($request);
    }

    private function bootApplication(?Path $path = null, array $providers = [])
    {
        $this->app = new App($path);

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        $this->app->register(new DotEnvServiceProvider());
        $this->app->boot();
        $this->app->getContainer()->get('env');
    }

    private function createSlimApplication(): SlimApp
    {
        $container = $this->app->getContainer();
        if (! $container->has(RouteCollectorInterface::class)) {
            return AppFactory::createFromContainer($container);
        }
        $this->routeCollector = $container->get(RouteCollectorInterface::class);

        $app = AppFactory::createFromContainer($container);
        $app->addBodyParsingMiddleware();
        return $app;
    }
}
