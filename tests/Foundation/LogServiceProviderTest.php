<?php

namespace Tests\Foundation;

use Gravatalonga\Container\Container;
use Gravatalonga\DriverManager\ExceptionManager;
use Gravatalonga\DriverManager\Manager;
use Gravatalonga\Framework\ValueObject\Path;
use Gravatalonga\KingFoundation\LogServiceProvider;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\TagProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries ()
    {
        $service = new LogServiceProvider();
        $entries = $service->factories();
        $extends = $service->extensions();

        $this->assertNotEmpty($entries);
        $this->assertEmpty($extends);
        $this->assertArrayHasKey('logger.manager', $entries);
        $this->assertArrayHasKey('logger.handler.single', $entries);
        $this->assertArrayHasKey(LoggerInterface::class, $entries);
    }

    /**
     * @test
     */
    public function can_get_manager_if_container_is_empty ()
    {
        $service = new LogServiceProvider();
        $container = new Container();
        $entries = $service->factories();

        $manager = $entries['logger.manager']($container);

        $this->assertInstanceOf(Manager::class, $manager);
    }

    /**
     * @test
     */
    public function can_build_manager_from_configuration_of_container ()
    {
        $service = new LogServiceProvider();
        $container = new Container([
            'config.log' => [
                'driver' => 'default',

                'drivers' => [
                    'default' => [
                        'level' => \Monolog\Level::Warning,
                        'name' => 'log.txt',
                        'handler' => ['single']
                    ]
                ]
            ]

        ]);
        $entries = $service->factories();

        /** @var Manager $manager */
        $manager = $entries['logger.manager']($container);
        $driver = $manager->driver('default');

        $this->assertIsArray($driver);
        $this->assertNotEmpty($driver);
        $this->assertEquals(\Monolog\Level::Warning, $driver['level']);
        $this->assertEquals('log.txt', $driver['name']);
    }

    /**
     * @test
     */
    public function driver_must_throw_exception_if_not_provider_required_requirment_level ()
    {
        $this->expectException(ExceptionManager::class);
        $this->expectExceptionMessage(ExceptionManager::driverMissingRequiredKey('default', 'leve')->getMessage());

        $service = new LogServiceProvider();
        $container = new Container([
            'config.log' => [
                'driver' => 'default',

                'drivers' => [
                    'default' => [
                        'name' => 'log.txt',
                        'handler' => []
                    ]
                ]
            ]

        ]);
        $entries = $service->factories();

        $entries['logger.manager']($container);
    }

    /**
     * @test
     */
    public function driver_must_throw_exception_if_not_provider_required_requirment_handler ()
    {
        $this->expectException(ExceptionManager::class);
        $this->expectExceptionMessage(ExceptionManager::driverMissingRequiredKey('default', 'handler')->getMessage());

        $service = new LogServiceProvider();
        $container = new Container([
            'config.log' => [
                'driver' => 'default',

                'drivers' => [
                    'default' => [
                        'name' => 'log.txt',
                        'level' => \Monolog\Level::Warning,
                    ]
                ]
            ]

        ]);
        $entries = $service->factories();

        $entries['logger.manager']($container);
    }

    /**
     * @test
     */
    public function driver_have_default_parameters_processor ()
    {
        $service = new LogServiceProvider();
        $container = new Container([
            'config.log' => [
                'driver' => 'default',

                'drivers' => [
                    'default' => [
                        'name' => 'log.txt',
                        'level' => \Monolog\Level::Warning,
                        'handler' => ['null']
                    ]
                ]
            ]

        ]);
        $entries = $service->factories();

        /** @var Manager $manager */
        $manager = $entries['logger.manager']($container);

        $this->assertArrayHasKey('processor', $manager->driver('default'));
    }

    /**
     * @test
     */
    public function can_get_stream_handler ()
    {
        $service = new LogServiceProvider();
        $container = new Container([
            'path.storage' => new Path('./tests/stub'),
            'config.log' => [
                'driver' => 'default',

                'drivers' => [
                    'default' => [
                        'level' => \Monolog\Level::Warning,
                        'name' => 'log.txt',
                        'handler' => ['single']
                    ]
                ]
            ]

        ]);
        $entries = $service->factories();
        $container->share('logger.manager', $entries['logger.manager']($container));
        $handler = $entries['logger.handler.single']($container);

        $this->assertNotEmpty($handler);
        $this->assertInstanceOf(StreamHandler::class, $handler);
    }

    /**
     * @test
     */
    public function can_create_log_instance_with_null_handler ()
    {
        $service = new LogServiceProvider();
        $container = new Container([
            'path.storage' => new Path('./tests/stub'),
            'config.log' => [
                'driver' => 'default',

                'drivers' => [
                    'default' => [
                        'level' => \Monolog\Level::Warning,
                        'name' => 'log.txt',
                        'handler' => ['null']
                    ]
                ]
            ]

        ]);
        $entries = $service->factories();
        $container->share('logger.manager', $entries['logger.manager']($container));
        $container->share('logger.handler.null', $entries['logger.handler.null']($container));
        $logger = $entries[LoggerInterface::class]($container);

        $this->assertNotEmpty($logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);

        $this->assertNotEmpty($logger->getHandlers());
        $this->assertCount(1, $logger->getHandlers());
        $this->assertInstanceOf(NullHandler::class, $logger->getHandlers()[0]);
    }

    /**
     * @test
     */
    public function can_create_log_instance_with_processor ()
    {
        $service = new LogServiceProvider();
        $container = new Container([
            'path.storage' => new Path('./tests/stub'),
            'config.log' => [
                'driver' => 'default',

                'drivers' => [
                    'default' => [
                        'level' => \Monolog\Level::Warning,
                        'name' => 'log.txt',
                        'handler' => ['null'],
                        'processor' => ['tag']
                    ]
                ]
            ],
            'logger.processor.tag' => new TagProcessor(['super'])

        ]);
        $entries = $service->factories();
        $container->share('logger.manager', $entries['logger.manager']($container));
        $container->share('logger.handler.null', $entries['logger.handler.null']($container));
        $logger = $entries[LoggerInterface::class]($container);

        $this->assertNotEmpty($logger);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);

        $this->assertNotEmpty($logger->getProcessors());
        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(TagProcessor::class, $logger->getProcessors()[0]);
    }

}