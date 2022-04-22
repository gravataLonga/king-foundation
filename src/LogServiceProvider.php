<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Gravatalonga\DriverManager\Manager;
use Gravatalonga\Framework\ServiceProvider;
use Gravatalonga\Framework\ValueObject\Path;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LogServiceProvider implements ServiceProvider
{
    public function factories(): array
    {
        return [
            'logger.manager' => function (ContainerInterface $container) {
                return new Manager($container->has('config.log') ? $container->get('config.log')['drivers'] ?? [] : []);
            },
            LoggerInterface::class => function (ContainerInterface $container) {
                $storage = $container->has('path.storage') ? $container->get('path.storage') : new Path(__DIR__);
                $app = $container->has('config.app') ? $container->get('config.app') : [];
                $logConfiguration = $container->has('config.log') ? $container->get('config.log') : [];

                /** @var Manager $driver */
                $driver = $container->get('logger.manager');
                $log = $driver->driver($logConfiguration['driver'] ?? 'default');

                $logger = new Logger($app['name'] ?? 'APP');
                $level = Logger::toMonologLevel($log['level']);

                $logger->pushHandler(new StreamHandler($storage->suffix('log') . '/' . $log['name'], $level));

                return $logger;
            },
        ];
    }

    public function extensions(): array
    {
        return [

        ];
    }
}
