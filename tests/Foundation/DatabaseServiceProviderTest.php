<?php

namespace Tests\Foundation;

use Doctrine\DBAL\Connection;
use Gravatalonga\KingFoundation\DatabaseServiceProvider;
use PHPUnit\Framework\TestCase;

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
}
