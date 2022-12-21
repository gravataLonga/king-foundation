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
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Gravatalonga\Framework\ServiceProvider;
use Gravatalonga\KingFoundation\ConsoleServiceProvider;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use function Gravatalonga\Framework\container;
use function Gravatalonga\Framework\instance;
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
    }

    /**
     * @test
     */
    public function can_create_connection_from_service_provider()
    {
        new Kernel(null, [
            new class() implements ServiceProvider
            {
                public function factories(): array
                {
                    return [
                        'config.console' => []
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
            new class() implements ServiceProvider
            {
                public function factories(): array
                {
                    return [
                        'config.console' => []
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
    public function it_bind_commands_to_applications ()
    {
        new Kernel(null, [
            new class() implements ServiceProvider
            {
                public function factories(): array
                {
                    return [
                        'config.console' => []
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
        $consoles = array_values($consoles);


        $this->assertInstanceOf(CurrentCommand::class, $consoles[0]);
        $this->assertInstanceOf(DumpSchemaCommand::class, $consoles[1]);
        $this->assertInstanceOf(ExecuteCommand::class, $consoles[2]);
        $this->assertInstanceOf(GenerateCommand::class, $consoles[3]);
        $this->assertInstanceOf(LatestCommand::class, $consoles[4]);
        $this->assertInstanceOf(MigrateCommand::class, $consoles[5]);
        $this->assertInstanceOf(RollupCommand::class, $consoles[6]);
        $this->assertInstanceOf(StatusCommand::class, $consoles[7]);
        $this->assertInstanceOf(VersionCommand::class, $consoles[8]);
        $this->assertInstanceOf(UpToDateCommand::class, $consoles[9]);
        $this->assertInstanceOf(SyncMetadataCommand::class, $consoles[10]);
        $this->assertInstanceOf(ListCommand::class, $consoles[11]);
    }
}