<?php

namespace Tests\Foundation;

use Gravatalonga\Container\Container;
use Gravatalonga\KingFoundation\CallableResolver;
use Gravatalonga\KingFoundation\SlimServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;

/**
 * @covers \Gravatalonga\KingFoundation\SlimServiceProvider
 */
class SlimServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries()
    {
        $service = new SlimServiceProvider();
        $entries = $service->factories();

        $this->assertEmpty($service->extensions());
        $this->assertArrayHasKey(ResponseFactoryInterface::class, $entries);
        $this->assertArrayHasKey(CallableResolverInterface::class, $entries);
        $this->assertArrayHasKey(RouteCollectorInterface::class, $entries);
    }

    /**
     * @test
     */
    public function can_create_response_factory()
    {
        $container = new Container();
        $service = new SlimServiceProvider();
        $entries = $service->factories();

        $factory = $entries[ResponseFactoryInterface::class]($container);

        $this->assertInstanceOf(ResponseFactoryInterface::class, $factory);
    }

    /**
     * @test
     */
    public function can_create_callable_resolver_interface()
    {
        $container = new Container();
        $service = new SlimServiceProvider();
        $entries = $service->factories();

        $callable = $entries[CallableResolverInterface::class]($container);

        $this->assertInstanceOf(CallableResolver::class, $callable);
    }

    /**
     * @test
     */
    public function can_create_router_collector()
    {
        $container = new Container([
            CallableResolverInterface::class => new CallableResolver(),
            ResponseFactoryInterface::class => AppFactory::determineResponseFactory(),
        ]);
        $service = new SlimServiceProvider();
        $entries = $service->factories();

        $router = $entries[RouteCollectorInterface::class]($container);

        $this->assertInstanceOf(RouteCollectorInterface::class, $router);
    }
}
