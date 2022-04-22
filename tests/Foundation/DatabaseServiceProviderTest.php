<?php

namespace Tests\Foundation;

use Doctrine\DBAL\Connection;
use Gravatalonga\KingFoundation\DatabaseServiceProvider;
use Gravatalonga\KingFoundation\Kernel;
use PHPUnit\Framework\TestCase;
use function Gravatalonga\Framework\container;
use function Gravatalonga\Framework\instance;

/**
 * @covers \Gravatalonga\Web\Foundation\DatabaseServiceProvider
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
    }

    /**
     * @test
     */
    public function can_create_connection_from_service_provider ()
    {
        new Kernel(null, [
            new DatabaseServiceProvider()
        ]);
        container()->set('config.databases', [
            'master' => [
                'charset' => 'UTF8',
                'memory' => true,
                'driver' => 'pdo_sqlite'
            ]
        ]);

        $connection = instance(Connection::class);

        $this->assertNotEmpty($connection);
        $this->assertInstanceOf(Connection::class, $connection);
    }
}