<?php

namespace Tests\Foundation\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Gravatalonga\KingFoundation\Database\Migration;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private Connection $connection;

    public function setUp(): void
    {
        $this->connection = DriverManager::getConnection(
            $this->database()
        );
    }

    /**
     * @test
     */
    public function can_migrate_latest_version ()
    {
        $factory = DependencyFactory::fromConnection(
            new ConfigurationArray($this->migration('correct')),
            new ExistingConnection($this->connection)
        );

        $migrate = new Migration($factory);
        $migrate->migrate();

        $records = $this->connection->fetchAllAssociative('SELECT * FROM migrations');

        $this->assertNotEmpty($records);
        $this->assertCount(1, $records);
        $this->assertEquals('Databases\Migrations\Version20221209130716', $records[0]['version']);
    }

    /**
     * @test
     */
    public function throw_exception_if_migration_is_empty ()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('migration is empty');

        $factory = DependencyFactory::fromConnection(
            new ConfigurationArray($this->migration('empty')),
            new ExistingConnection($this->connection)
        );

        $migrate = new Migration($factory);
        $migrate->migrate();
    }

    /**
     * @test
     */
    public function there_arent_any_new_migration_to_execute ()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('already in latest version');

        $factory = DependencyFactory::fromConnection(
            new ConfigurationArray($this->migration('correct')),
            new ExistingConnection($this->connection)
        );

        $migrate = new Migration($factory);
        $migrate->migrate();

        $migrate->migrate();
    }

    /**
     * @test
     */
    public function can_migrate_first_version ()
    {
        $factory = DependencyFactory::fromConnection(
            new ConfigurationArray($this->migration('correct')),
            new ExistingConnection($this->connection)
        );

        $migrate = new Migration($factory);
        $migrate->migrate();
        $migrate->migrate('first');

        $records = $this->connection->fetchAllAssociative('SELECT * FROM migrations');

        $this->assertEmpty($records);
    }

    public function database(): array
    {
        return [
            'charset' => 'UTF8',
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ];
    }

    public function migration(string $path): array
    {
        return [
            'table_storage' => [
                'table_name' => 'migrations',
                'version_column_name' => 'version',
                'version_column_length' => 1024,
                'executed_at_column_name' => 'executed_at',
                'execution_time_column_name' => 'execution_time',
            ],

            'migrations_paths' => [
                'Databases\Migrations' => './tests/stub/migrations/' . $path . '/'
            ],

            'all_or_nothing' => true,
            'transactional' => true,
            'check_database_platform' => true,
            'organize_migrations' => 'none',
            'connection' => null,
            'em' => null,
        ];
    }
}