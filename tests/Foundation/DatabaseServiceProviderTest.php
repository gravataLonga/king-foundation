<?php

namespace Tests\Foundation;

use Doctrine\DBAL\Connection;
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
use function Gravatalonga\Framework\container;
use function Gravatalonga\Framework\instance;
use Gravatalonga\Framework\ServiceProvider;
use Gravatalonga\KingFoundation\ConsoleServiceProvider;
use Gravatalonga\KingFoundation\Database\Migration;
use Gravatalonga\KingFoundation\DatabaseServiceProvider;
use Gravatalonga\KingFoundation\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Gravatalonga\KingFoundation\DatabaseServiceProvider
 */
class DatabaseServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries()
    {
        $provider = new DatabaseServiceProvider();
        $entries = $provider->factories();
        $extends = $provider->extensions();

        $this->assertNotEmpty($entries);
        $this->assertNotEmpty($extends);
        $this->assertArrayHasKey('database.connections', $entries);
        $this->assertArrayHasKey(Connection::class, $entries);
        $this->assertArrayHasKey(ConfigurationLoader::class, $entries);
        $this->assertArrayHasKey('database.migrations.factory', $entries);
        $this->assertArrayHasKey(Migration::class, $entries);
        $this->assertArrayHasKey('config.console', $extends);

        $this->assertArrayHasKey(CurrentCommand::class, $entries);
        $this->assertArrayHasKey(DumpSchemaCommand::class, $entries);
        $this->assertArrayHasKey(ExecuteCommand::class, $entries);
        $this->assertArrayHasKey(GenerateCommand::class, $entries);
        $this->assertArrayHasKey(LatestCommand::class, $entries);
        $this->assertArrayHasKey(MigrateCommand::class, $entries);
        $this->assertArrayHasKey(RollupCommand::class, $entries);
        $this->assertArrayHasKey(StatusCommand::class, $entries);
        $this->assertArrayHasKey(VersionCommand::class, $entries);
        $this->assertArrayHasKey(UpToDateCommand::class, $entries);
        $this->assertArrayHasKey(SyncMetadataCommand::class, $entries);
        $this->assertArrayHasKey(ListCommand::class, $entries);
    }

    /**
     * @test
     */
    public function can_create_connection_from_service_provider()
    {
        new Kernel(null, [
            new class() implements ServiceProvider {
                public function factories(): array
                {
                    return [
                        'config.console' => [],
                    ];
                }

                public function extensions(): array
                {
                    return [];
                }
            },
            new DatabaseServiceProvider(),
        ]);

        container()->set('config.databases', [
            'master' => [
                'charset' => 'UTF8',
                'memory' => true,
                'driver' => 'pdo_sqlite',
            ],
        ]);

        $connection = instance(Connection::class);

        $this->assertNotEmpty($connection);
        $this->assertInstanceOf(Connection::class, $connection);
    }

    /**
     * @test
     */
    public function can_create_dependency_factory()
    {
        new Kernel(null, [
            new class() implements ServiceProvider {
                public function factories(): array
                {
                    return [
                        'config.console' => [],
                    ];
                }

                public function extensions(): array
                {
                    return [];
                }
            },
            new DatabaseServiceProvider(),
        ]);

        container()->set('config.databases', [
            'master' => [
                'charset' => 'UTF8',
                'memory' => true,
                'driver' => 'pdo_sqlite',
            ],
        ]);
        container()->set('config.migrations', [
            'table_storage' => [
                'table_name' => 'migrations',
                'version_column_name' => 'version',
                'version_column_length' => 1024,
                'executed_at_column_name' => 'executed_at',
                'execution_time_column_name' => 'execution_time',
            ],

            'migrations_paths' => [
                'Databases\Migrations' => './resource/databases',
            ],

            'all_or_nothing' => true,
            'transactional' => true,
            'check_database_platform' => true,
            'organize_migrations' => 'none',
            'connection' => null,
            'em' => null,
        ]);

        $factory = instance('database.migrations.factory');

        $this->assertNotEmpty($factory);
        $this->assertInstanceOf(DependencyFactory::class, $factory);
    }

    /**
     * @test
     */
    public function it_bind_commands_to_applications()
    {
        new Kernel(null, [
            new class() implements ServiceProvider {
                public function factories(): array
                {
                    return [
                        'config.console' => [],
                    ];
                }

                public function extensions(): array
                {
                    return [];
                }
            },
            new ConsoleServiceProvider(),
            new DatabaseServiceProvider(),
        ]);

        container()->set('config.databases', [
            'master' => [
                'charset' => 'UTF8',
                'memory' => true,
                'driver' => 'pdo_sqlite',
            ],
        ]);
        container()->set('config.migrations', [
            'table_storage' => [
                'table_name' => 'migrations',
                'version_column_name' => 'version',
                'version_column_length' => 1024,
                'executed_at_column_name' => 'executed_at',
                'execution_time_column_name' => 'execution_time',
            ],

            'migrations_paths' => [
                'Databases\Migrations' => './resource/databases',
            ],

            'all_or_nothing' => true,
            'transactional' => true,
            'check_database_platform' => true,
            'organize_migrations' => 'none',
            'connection' => null,
            'em' => null,
        ]);

        $consoles = instance('config.console');

        $this->assertArrayHasKey('migrations:current', $consoles);
        $this->assertArrayHasKey('migrations:dump-schema', $consoles);
        $this->assertArrayHasKey('migrations:execute', $consoles);
        $this->assertArrayHasKey('migrations:generate', $consoles);
        $this->assertArrayHasKey('migrations:latest', $consoles);
        $this->assertArrayHasKey('migrations:migrate', $consoles);
        $this->assertArrayHasKey('migrations:rollup', $consoles);
        $this->assertArrayHasKey('migrations:status', $consoles);
        $this->assertArrayHasKey('migrations:version', $consoles);
        $this->assertArrayHasKey('migrations:up-to-date', $consoles);
        $this->assertArrayHasKey('migrations:sync-metadata-storage', $consoles);
        $this->assertArrayHasKey('migrations:list', $consoles);

        $this->assertContains(CurrentCommand::class, $consoles);
        $this->assertContains(DumpSchemaCommand::class, $consoles);
        $this->assertContains(ExecuteCommand::class, $consoles);
        $this->assertContains(GenerateCommand::class, $consoles);
        $this->assertContains(LatestCommand::class, $consoles);
        $this->assertContains(MigrateCommand::class, $consoles);
        $this->assertContains(RollupCommand::class, $consoles);
        $this->assertContains(StatusCommand::class, $consoles);
        $this->assertContains(VersionCommand::class, $consoles);
        $this->assertContains(UpToDateCommand::class, $consoles);
        $this->assertContains(SyncMetadataCommand::class, $consoles);
        $this->assertContains(ListCommand::class, $consoles);
    }
}
