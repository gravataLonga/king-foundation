<?php

namespace Gravatalonga\KingFoundation;

use Gravatalonga\Framework\ServiceProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class ConsoleServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            CommandLoaderInterface::class => function(ContainerInterface $container) {
                return new ContainerCommandLoader($container, $container->has('config.console') ? $container->get('config.console') : []);
            },
            Application::class => function(ContainerInterface $container) {

                $config = $container->has('config.app') ? $container->get('config.app') : [];
                $name = $config['name'] ?? 'UNKNOWN';
                $version = $config['version'] ?? 'UNKNOWN';

                $app = new Application($name, $version);
                if ($container->has(CommandLoaderInterface::class)) {
                    $app->setCommandLoader($container->get(CommandLoaderInterface::class));
                }
                return $app;
            }
        ];
    }

    public function extensions(): array
    {
        return [];
    }
}