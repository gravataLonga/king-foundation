<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Gravatalonga\Framework\ServiceProvider;
use League\Tactician\CommandBus;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;
use Psr\Container\ContainerInterface;

class CommandBusServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            ContainerLocator::class => function (ContainerInterface $container) {
                return new ContainerLocator($container, $container->has('config.commands') ? $container->get('config.commands') : []);
            },
            'bus.middleware.lock' => function (ContainerInterface $container) {
                return new LockingMiddleware();
            },
            'bus.middleware.command' => function (ContainerInterface $container) {
                return new CommandHandlerMiddleware(
                    new ClassNameExtractor(),
                    $container->get(ContainerLocator::class),
                    new HandleInflector()
                );
            },
            'bus.middleware' => function (ContainerInterface $container) {
                return [
                    'lock',
                    'command',
                ];
            },
            CommandBus::class => function (ContainerInterface $container) {
                $middlewares = $container->get('bus.middleware');
                $middlewaresInstances = [];
                foreach ($middlewares as $middleware) {
                    if (! $container->has('bus.middleware.'.$middleware)) {
                        continue;
                    }
                    $middlewaresInstances = $container->get('bus.middleware.'.$middleware);
                }

                return new CommandBus($middlewaresInstances);
            },
        ];
    }

    public function extensions(): array
    {
        return [

        ];
    }
}
