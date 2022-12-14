<?php

namespace Gravatalonga\KingFoundation\Database;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Exception\NoMigrationsFoundWithCriteria;
use Doctrine\Migrations\Exception\NoMigrationsToExecute;
use Doctrine\Migrations\Exception\UnknownMigrationVersion;
use Doctrine\Migrations\MigratorConfiguration;
use Symfony\Component\Console\Formatter\OutputFormatter;

final class Migration
{
    /**
     * @var \Doctrine\Migrations\DependencyFactory
     */
    private DependencyFactory $factory;

    public function __construct(DependencyFactory $factory)
    {
        $this->factory = $factory;
        $this->factory->getMetadataStorage()->ensureInitialized();
    }

    private function getMigratorConfiguration(): MigratorConfiguration
    {
        $migration = new MigratorConfiguration();
        return $migration->setDryRun(false)
            ->setAllOrNothing(true);
    }

    public function migrate(string $versionStr = 'latest'): array
    {
        $migrationRepository = $this->factory->getMigrationRepository();

        if (count($migrationRepository->getMigrations()) === 0) {
            throw new \LogicException('migration is empty');
        }

        $version = $this->factory->getVersionAliasResolver()->resolveVersionAlias($versionStr);

        $planCalculator                = $this->factory->getMigrationPlanCalculator();
        $statusCalculator              = $this->factory->getMigrationStatusCalculator();
        // $executedUnavailableMigrations = $statusCalculator->getExecutedUnavailableMigrations();

        $plan = $planCalculator->getPlanUntilVersion($version);

        if (count($plan) === 0) {
            throw new \LogicException(sprintf('already in %s version', $versionStr));
        }

        $migrator = $this->factory->getMigrator();
        return $migrator->migrate($plan, $this->getMigratorConfiguration());
    }
}