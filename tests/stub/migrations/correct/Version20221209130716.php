<?php

declare(strict_types=1);

namespace Databases\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221209130716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('user');
        $table->addColumn('name', 'string')->setNotnull(false);
        $table->addColumn('email', 'string')->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user');

    }
}
