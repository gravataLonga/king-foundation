<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use Gravatalonga\DriverManager\Manager;
use Gravatalonga\Framework\ServiceProvider;
use Gravatalonga\Framework\ValueObject\Path;
use Monolog\Handler\NullHandler;
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
                $configuration = $container->has('config.log') ? $container->get('config.log') : [];

                return new Manager(
                    $configuration['drivers'] ?? [],
                    ['level', 'handler'],
                    ['processor' => []]
                );
            },
            'logger.handler.null' => function (ContainerInterface $container) {
                return new NullHandler();
            },
            'logger.handler.single' => function (ContainerInterface $container) {
                /** @var Path $storage */
                $storage = $container->has('path.storage') ? $container->get('path.storage') : new Path(__DIR__);
                $logConfiguration = $container->has('config.log') ? $container->get('config.log') : [];

                /** @var Manager $driver */
                $driver = $container->get('logger.manager');
                $log = $driver->driver($logConfiguration['driver'] ?? 'default');
                $level = Logger::toMonologLevel($log['level']);

                return new StreamHandler($storage->suffix('log') . '/' . $log['name'], $level);
            },
            LoggerInterface::class => function (ContainerInterface $container) {
                $app = $container->has('config.app') ? $container->get('config.app') : [];
                $handlers = $container->has('logger.handler') ? $container->get('logger.handler') : [];

                $driver = $container->get('logger.manager');
                $log = $driver->driver($logConfiguration['driver'] ?? 'default');

                $logger = new Logger($app['name'] ?? 'APP', $handlers);

                if (empty($log['handler'])) {
                    return $logger;
                }

                $handlers = $log['handler'];
                foreach ($handlers as $handler) {
                    if ($container->has('logger.handler.' . $handler)) {
                        $handlerInstance = $container->get('logger.handler.' . $handler);
                        $logger->pushHandler($handlerInstance);
                    }
                }

                $processors = $log['processor'];
                foreach ($processors as $processor) {
                    if ($container->has('logger.processor.' . $processor)) {
                        $processoInstance = $container->get('logger.processor.' . $processor);
                        $logger->pushProcessor($processoInstance);
                    }
                }

                return $logger;
            },
        ];
    }

    public function extensions(): array
    {
        return [];
    }
}
