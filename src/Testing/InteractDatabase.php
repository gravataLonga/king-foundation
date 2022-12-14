<?php

namespace Gravatalonga\KingFoundation\Testing;

use Doctrine\DBAL\Connection;
use Gravatalonga\KingFoundation\Database\Migration;
use PHPUnit\Framework\Assert;

trait InteractDatabase
{
    public function migrate(string $versionAlias)
    {
        /**
         * @var Migration|null $migration
         */
        $migration = $this->container->has(Migration::class) ? $this->container->get(Migration::class) : null;

        if (empty($migration)) {
            throw new \Exception('missing createApp trait?');
        }

        $migration->migrate($versionAlias);
    }

    public function assertDatabaseHas(string $table, array $properties): void
    {
        /**
         * @var \Doctrine\DBAL\Connection|null $connection
         */
        $connection = $this->container->has(Connection::class) ? $this->container->get(Connection::class) : null;

        if (empty($migration)) {
            throw new \Exception('missing createApp trait?');
        }

        $where = array_map(function ($item) {
            return ':' . $item;
        }, array_keys($properties));

        $records = $connection->fetchAssociative('SELECT * FROM ' . $table . ' WHERE ' . implode(", ", $where), $properties);

        if (empty($records)) {
            Assert::fail(sprintf("Unable to find record %s", implode(', ', $properties)));
        }
    }

    public function assertDatabaseMissing(string $table, array $properties): void
    {
        /**
         * @var \Doctrine\DBAL\Connection|null $connection
         */
        $connection = $this->container->has(Connection::class) ? $this->container->get(Connection::class) : null;

        if (empty($migration)) {
            throw new \Exception('missing createApp trait?');
        }

        $where = array_map(function ($item) {
            return ':' . $item;
        }, array_keys($properties));

        $records = $connection->fetchAssociative('SELECT * FROM ' . $table . ' WHERE ' . implode(", ", $where), $properties);

        if (! empty($records)) {
            Assert::fail(sprintf("Was to find record %s", implode(', ', $properties)));
        }
    }
}
