<?php declare(strict_types=1);

namespace Tests\Foundation;

use Gravatalonga\Container\Container;
use Gravatalonga\KingFoundation\CommandBusServiceProvider;
use League\Tactician\CommandBus;
use League\Tactician\Container\ContainerLocator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Gravatalonga\KingFoundation\CommandBusServiceProvider
 */
class CommandBusServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries()
    {
        $provider = new CommandBusServiceProvider();
        $entries = $provider->factories();

        $this->assertArrayHasKey(ContainerLocator::class, $entries);
        $this->assertArrayHasKey(CommandBus::class, $entries);
        $this->assertArrayHasKey('bus.middleware', $entries);
        $this->assertArrayHasKey('bus.middleware.command', $entries);
        $this->assertArrayHasKey('bus.middleware.lock', $entries);
    }

    /**
     * @test
     */
    public function can_built_container_locator()
    {
        $container = new Container();
        $provider = new CommandBusServiceProvider();
        $entries = $provider->factories();

        $instance = $entries[ContainerLocator::class]($container);

        $this->assertInstanceOf(ContainerLocator::class, $instance);
    }

    /**
     * @test
     */
    public function can_built_command_bus()
    {
        $container = new Container(['bus.middleware' => ['lock', 'command']]);
        $container->share(ContainerLocator::class, function (ContainerInterface $container) {
            return new ContainerLocator($container, []);
        });
        $provider = new CommandBusServiceProvider();
        $entries = $provider->factories();

        $instance = $entries[CommandBus::class]($container);

        $this->assertInstanceOf(CommandBus::class, $instance);
    }
}
