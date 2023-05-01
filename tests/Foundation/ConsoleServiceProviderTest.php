<?php declare(strict_types=1);

namespace Tests\Foundation;

use function Gravatalonga\Framework\container;
use function Gravatalonga\Framework\instance;
use Gravatalonga\Framework\ServiceProvider;
use Gravatalonga\KingFoundation\ConsoleServiceProvider;
use Gravatalonga\KingFoundation\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\ApplicationTester;

class ConsoleServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries()
    {
        $provider = new ConsoleServiceProvider();

        $this->assertNotEmpty($provider->factories());
        $this->assertEmpty($provider->extensions());
        $this->assertIsArray($provider->factories());
        $this->assertInstanceOf(ServiceProvider::class, $provider);
        $this->assertArrayHasKey(Application::class, $provider->factories());
        $this->assertArrayHasKey(CommandLoaderInterface::class, $provider->factories());
    }

    /**
     * @test
     */
    public function can_create_a_console_application()
    {
        new Kernel(null, [
            new ConsoleServiceProvider(),
        ]);

        container()->set('config.app', [
            'name' => 'App',
            'version' => '1.0.0',
        ]);

        /** @var Application $application */
        $application = instance(Application::class);
        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals('App', $application->getName());
        $this->assertEquals('1.0.0', $application->getVersion());
    }

    /**
     * @test
     */
    public function exists_config_but_set_keys_for_name_and_version()
    {
        new Kernel(null, [
            new ConsoleServiceProvider(),
        ]);

        container()->set('config.app', []);

        /** @var Application $application */
        $application = instance(Application::class);
        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals('UNKNOWN', $application->getName());
        $this->assertEquals('UNKNOWN', $application->getVersion());
    }

    /**
     * @test
     */
    public function can_register_commands()
    {
        new Kernel(null, [
            new ConsoleServiceProvider(),
        ]);

        container()->set('command_app_debug', new class() extends Command {
            protected function configure()
            {
                $this->setName('app:debug')
                    ->setDescription('Application Debug');
            }

            public function execute(InputInterface $input, OutputInterface $output)
            {
                $output->write('SUCCESS');

                return Command::SUCCESS;
            }
        });
        container()->set('config.console', [
            'app:debug' => 'command_app_debug',
        ]);

        /** @var Application $application */
        $application = instance(Application::class);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);
        $int = $tester->run(['app:debug']);

        $this->assertEquals(Command::SUCCESS, $int);
        $this->assertEquals('SUCCESS', $tester->getDisplay());
    }
}
