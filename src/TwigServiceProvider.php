<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Gravatalonga\Framework\ServiceProvider;
use Gravatalonga\Framework\ValueObject\Path;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            'twig.loader' => function (ContainerInterface $container) {
                $storage = $container->has('path.resource') ? $container->get('path.resource') : new Path(__DIR__);

                return new FilesystemLoader($storage->suffix('views'));
            },
            'twig.options' => function (ContainerInterface $container) {
                $storage = $container->has('path.storage') ? $container->get('path.storage') : new Path(__DIR__);
                $options = $container->has('config.twig') ? $container->get('config.twig') : [];

                return array_merge($options, [
                    'cache' => (string)$storage->suffix('cache/view'),
                ]);
            },
            Environment::class => function (ContainerInterface $container) {
                $options = $container->has('twig.options') ? $container->get('twig.options') : [];

                return new Environment($container->get('twig.loader'), $options);
            },
        ];
    }

    public function extensions(): array
    {
        return [];
    }
}