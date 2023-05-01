<?php declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Dotenv\Dotenv;
use Gravatalonga\Framework\ServiceProvider;
use Psr\Container\ContainerInterface;

final class DotEnvServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            'env' => function (ContainerInterface $container) {
                $dotenv = Dotenv::createImmutable($container->has('path.base') ? (string)$container->get('path.base') : __DIR__);
                $dotenv->safeLoad();

                return $dotenv;
            },
        ];
    }

    public function extensions(): array
    {
        return [

        ];
    }
}
