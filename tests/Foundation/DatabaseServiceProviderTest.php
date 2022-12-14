<?php

namespace Tests\Foundation;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\DependencyFactory;
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

        $this->assertNotEmpty($entries);
        $this->assertArrayHasKey('database.connections', $entries);
        $this->assertArrayHasKey(Connection::class, $entries);
        $this->assertArrayHasKey(ConfigurationLoader::class, $entries);
        $this->assertArrayHasKey('database.migrations.factory', $entries);
        $this->assertArrayHasKey(Migration::class, $entries);
    }

    /**
     * @test
     */
    public function can_create_connection_from_service_provider()
    {
        new Kernel(null, [
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
}
