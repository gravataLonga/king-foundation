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
            'twig.filter' => function (ContainerInterface $container) {
                return [];
            },
            'twig.global' => function (ContainerInterface $container) {
                return [];
            },
            'twig.function' => function (ContainerInterface $container) {
                return [];
            },
            'twig.test' => function (ContainerInterface $container) {
                return [];
            },
            'twig.extension' => function (ContainerInterface $container) {
                return [];
            },
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

                $twig = new Environment($container->get('twig.loader'), $options);

                // Filters...
                if ($container->has('twig.filter')) {
                    $filters = $container->get('twig.filter');
                    foreach ($filters as $filter) {
                        if (! $container->has('twig.filter.'.$filter)) {
                            continue;
                        }

                        $twig->addFilter($container->get('twig.filter.'.$filter));
                    }
                }

                // Globals...
                if ($container->has('twig.global')) {
                    $globals = $container->get('twig.global');
                    foreach ($globals as $global) {
                        if (! $container->has('twig.global.'.$global)) {
                            continue;
                        }

                        $twig->addGlobal($global, $container->get('twig.global.'.$global));
                    }
                }

                // Functions...
                if ($container->has('twig.function')) {
                    $functions = $container->get('twig.function');
                    foreach ($functions as $fn) {
                        if (! $container->has('twig.function.'.$fn)) {
                            continue;
                        }

                        $twig->addFunction($container->get('twig.function.'.$fn));
                    }
                }

                // Tests...
                if ($container->has('twig.test')) {
                    $tests = $container->get('twig.test');
                    foreach ($tests as $test) {
                        if (! $container->has('twig.test.'.$test)) {
                            continue;
                        }

                        $twig->addTest($container->get('twig.test.'.$test));
                    }
                }

                // Extensions...
                if ($container->has('twig.extension')) {
                    $extensions = $container->get('twig.extension');
                    foreach ($extensions as $ext) {
                        if (! $container->has('twig.extension.'.$ext)) {
                            continue;
                        }

                        $twig->addExtension($container->get('twig.extension.'.$ext));
                    }
                }

                return $twig;
            },
            'twig' => function (ContainerInterface $container) {
                return $container->get(Environment::class);
            },
        ];
    }

    public function extensions(): array
    {
        return [];
    }
}
