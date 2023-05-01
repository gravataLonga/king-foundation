<?php declare(strict_types=1);

namespace Tests\Foundation;

use Gravatalonga\Container\Container;
use Gravatalonga\KingFoundation\CallableResolver;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Gravatalonga\KingFoundation\CallableResolver
 */
class CallableResolverTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_resolve_callback()
    {
        $container = new Container();
        $callableResolver = new CallableResolver($container);

        $callback = $callableResolver->resolve(function () {
            return 1;
        });

        $this->assertIsCallable($callback);
        $this->assertEquals(1, $callback());
    }

    /**
     * @test
     */
    public function it_resolve_without_container()
    {
        $callableResolver = new CallableResolver();

        $callback = $callableResolver->resolve(function () {
            return 1;
        });

        $this->assertIsCallable($callback);
        $this->assertEquals(1, $callback());
    }

    /**
     * @test
     */
    public function it_bind_container_to_callback()
    {
        $container = new Container();
        $container->set('number', 1);
        $callableResolver = new CallableResolver($container);

        $callback = $callableResolver->resolve(function () {
            return $this->get('number') + 1;
        });

        $this->assertIsCallable($callback);
        $this->assertEquals(2, $callback());
    }

    /**
     * @test
     */
    public function resolve_by_array_and_without_container()
    {
        $callableResolver = new CallableResolver();

        $callback = $callableResolver->resolve([StubMethod::class, 'callMe']);

        $this->assertIsCallable($callback);
        $this->assertEquals(1, $callback());
    }

    /**
     * @test
     */
    public function resolve_class_with_invoke_without_container()
    {
        $callableResolver = new CallableResolver();

        $callback = $callableResolver->resolve(StubInvoke::class);

        $this->assertIsCallable($callback);
        $this->assertEquals(1, $callback());
    }

    /**
     * @test
     */
    public function resolve_class_by_invoke_with_container()
    {
        $container = new Container();
        $container->set('number', 1);
        $callableResolver = new CallableResolver($container);

        $callback = $callableResolver->resolve(StubInvokeContainer::class);

        $this->assertIsCallable($callback);
        $this->assertEquals(2, $callback());
    }

    /**
     * @test
     */
    public function can_resolve_class_by_notion_string()
    {
        $callableResolver = new CallableResolver();

        $callback = $callableResolver->resolve(StubMethod::class. '@callMe');

        $this->assertIsCallable($callback);
        $this->assertEquals(1, $callback());
    }

    /**
     * @test
     */
    public function can_resolve_class_by_notion_with_container()
    {
        $callableResolver = new CallableResolver(new Container(['number' => 1]));

        $callback = $callableResolver->resolve(StubMethodContainer::class. '@callMe');

        $this->assertIsCallable($callback);
        $this->assertEquals(2, $callback());
    }
}

class StubMethod
{
    public function callMe(): int
    {
        return 1;
    }
}

class StubInvoke
{
    public function __invoke(): int
    {
        return 1;
    }
}

class StubMethodContainer
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function callMe(): int
    {
        return $this->container->get('number') + 1;
    }
}

class StubInvokeContainer
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(): int
    {
        return $this->container->get('number') + 1;
    }
}
