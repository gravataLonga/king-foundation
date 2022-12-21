<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
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
            ConfigurationLoader::class => function (ContainerInterface $container) {
                return new ConfigurationArray($container->has('config.migrations') ? $container->get('config.migrations') : []);
            },
            'database.migrations.factory' => function (ContainerInterface $container) {
                $config = $container->has(ConfigurationLoader::class) ? $container->get(ConfigurationLoader::class) : null;
                $connection = $container->has(Connection::class) ? $container->get(Connection::class) : null;

                return DependencyFactory::fromConnection($config, new ExistingConnection($connection));
            },
            Migration::class => function (ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;

                return new Migration($factory);
            },

            // Commands
            CurrentCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }

                return new CurrentCommand($factory);
            },
            DumpSchemaCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }

                return new DumpSchemaCommand($factory);
            },
            ExecuteCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new ExecuteCommand($factory);
            },
            GenerateCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new GenerateCommand($factory);
            },
            LatestCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new LatestCommand($factory);
            },
            MigrateCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new MigrateCommand($factory);
            },
            RollupCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new RollupCommand($factory);
            },
            StatusCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new StatusCommand($factory);
            },
            VersionCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new VersionCommand($factory);
            },
            UpToDateCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new UpToDateCommand($factory);
            },
            SyncMetadataCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new SyncMetadataCommand($factory);
            },
            ListCommand::class => function(ContainerInterface $container) {
                $factory = $container->has('database.migrations.factory') ? $container->get('database.migrations.factory') : null;
                if ($factory === null) {
                    return null;
                }
                return new ListCommand($factory);
            },
        ];
    }

    public function extensions(): array
    {
        return [
            'config.console' => function (ContainerInterface $container, array $previous = []) {
                return array_merge([
                    'migrations:current' => CurrentCommand::class,
                    'migrations:dump-schema' => DumpSchemaCommand::class,
                    'migrations:execute' => ExecuteCommand::class,
                    'migrations:generate' => GenerateCommand::class,
                    'migrations:latest' => LatestCommand::class,
                    'migrations:migrate' => MigrateCommand::class,
                    'migrations:rollup' => RollupCommand::class,
                    'migrations:status' => StatusCommand::class,
                    'migrations:version' => VersionCommand::class,
                    'migrations:up-to-date' => UpToDateCommand::class,
                    'migrations:sync-metadata-storage' => SyncMetadataCommand::class,
                    'migrations:list' => ListCommand::class
                ], $previous);
            },
        ];
    }
}
