<?php declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Gravatalonga\Framework\ServiceProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteCollector;

final class SlimServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            ResponseFactoryInterface::class => function () {
                return AppFactory::determineResponseFactory();
            },
            CallableResolverInterface::class => function (ContainerInterface $container) {
                return new CallableResolver($container);
            },
            RouteCollectorInterface::class => function (ContainerInterface $container) {
                return new RouteCollector(
                    $container->get(ResponseFactoryInterface::class),
                    $container->get(CallableResolverInterface::class),
                    $container
                );
            },
        ];
    }

    public function extensions(): array
    {
        return [];
    }
}
