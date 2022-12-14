<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Gravatalonga\DriverManager\Manager;
use Gravatalonga\Framework\ServiceProvider;
use Gravatalonga\KingFoundation\Database\Migration;
use Psr\Container\ContainerInterface;

class DatabaseServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            'database.connections' => function (ContainerInterface $container) {
                $config = $container->has('config.databases') ? $container->get('config.databases') : [];

                return new Manager($config);
            },
            Connection::class => function (ContainerInterface $container) {
                $driver = $container->get('database.connections');

                return DriverManager::getConnection(
                    $driver->driver($_ENV['DATABASE_CONNECTION'] ?? 'master')
                );
            },
            ConfigurationLoader::class => function(ContainerInterface $container) {
                return new ConfigurationArray($container->has('config.migrations') ? $container->get('config.migrations') : []);
            },
            'database.migrations.factory' => function(ContainerInterface $container) {
                $config = $container->has(ConfigurationLoader::class) ? $container->get(ConfigurationLoader::class) : null;
                $connection = $container->has(Connection::class) ? $container->get(Connection::class) : null;
                return DependencyFactory::fromConnection($config, new ExistingConnection($connection));
            },
            Migration::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                return new Migration($factory);
            }
        ];
    }

    public function extensions(): array
    {
        return [];
    }
}
