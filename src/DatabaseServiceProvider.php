<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Gravatalonga\DriverManager\Manager;
use Gravatalonga\Framework\ServiceProvider;
use Psr\Container\ContainerInterface;

class DatabaseServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            'database.connections' => function (ContainerInterface $container) {
                return new Manager($container->get('config.databases'));
            },
            Connection::class => function (ContainerInterface $container) {
                $driver = $container->get('databases.connections');

                return DriverManager::getConnection(
                    $driver->driver($_ENV['DATABASE_CONNECTION'] ?? 'master')
                );
            },
        ];
    }

    public function extensions(): array
    {
        return [];
    }
}
