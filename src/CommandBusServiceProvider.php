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
            CommandBus::class => function (ContainerInterface $container) {
                $handlerMiddleware = new CommandHandlerMiddleware(
                    new ClassNameExtractor(),
                    $container->get(ContainerLocator::class),
                    new HandleInflector()
                );

                $lockingMiddleware = new LockingMiddleware();
                return new CommandBus([$lockingMiddleware, $handlerMiddleware]);
            }
        ];
    }

    public function extensions(): array
    {
        return [

        ];
    }
}