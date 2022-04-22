<?php

declare(strict_types=1);

namespace Gravatalonga\KingFoundation;

use function class_exists;
use Closure;
use function is_array;
use function is_callable;
use function is_object;

use function is_string;
use function json_encode;
use function preg_match;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;
use function sprintf;

final class CallableResolver implements CallableResolverInterface
{
    /**
     * @var string
     */
    public static $callablePattern = '!^([^\@]+)\@([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

    /**
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($toResolve): callable
    {
        $toResolve = $this->prepareToResolve($toResolve);
        if (is_callable($toResolve)) {
            return $this->bindToContainer($toResolve);
        }
        $resolved = $toResolve;
        if (is_string($toResolve)) {
            $resolved = $this->resolveGravatalongaNotion($toResolve);
            $resolved[1] = $resolved[1] ?? '__invoke';
        }
        $callable = $this->assertCallable($resolved, $toResolve);

        return $this->bindToContainer($callable);
    }

    /**
     * @param string $toResolve
     *
     * @throws RuntimeException
     *
     * @return array{object, string|null} [Instance, Method Name]
     */
    private function resolveGravatalongaNotion(string $toResolve): array
    {
        preg_match(CallableResolver::$callablePattern, $toResolve, $matches);
        [$class, $method] = $matches ? [$matches[1], $matches[2]] : [$toResolve, null];

        /** @var string $class */
        /** @var string|null $method */
        if ($this->container && $this->container->has($class)) {
            $instance = $this->container->get($class);
            if (! is_object($instance)) {
                throw new RuntimeException(sprintf('%s container entry is not an object', $class));
            }
        } elseif (! $this->container) {
            if (! class_exists($class)) {
                if ($method) {
                    $class .= '::' . $method . '()';
                }

                throw new RuntimeException(sprintf('Callable %s does not exist', $class));
            }
            $instance = new $class($this->container);

            return [$instance, $method];
        } else {
            if (! class_exists($class)) {
                if ($method) {
                    $class .= '::' . $method . '()';
                }

                throw new RuntimeException(sprintf('Callable %s does not exist', $class));
            }

            try {
                $instance = $this->container->get($class);
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                throw new RuntimeException(sprintf('Class can\'t be resolve by container - autowiring %s', $class));
            }
        }

        return [$instance, $method];
    }

    /**
     * @param mixed $resolved
     * @param mixed $toResolve
     *
     * @throws RuntimeException
     *
     * @return callable
     */
    private function assertCallable($resolved, $toResolve): callable
    {
        if (! is_callable($resolved)) {
            if (is_callable($toResolve) || is_object($toResolve) || is_array($toResolve)) {
                $formatedToResolve = ($toResolveJson = json_encode($toResolve)) !== false ? $toResolveJson : '';
            } else {
                $formatedToResolve = is_string($toResolve) ? $toResolve : '';
            }

            throw new RuntimeException(sprintf('%s is not resolvable', $formatedToResolve));
        }

        return $resolved;
    }

    /**
     * @param callable $callable
     *
     * @return callable
     */
    private function bindToContainer(callable $callable): callable
    {
        if (is_array($callable) && $callable[0] instanceof Closure) {
            $callable = $callable[0];
        }
        if ($this->container && $callable instanceof Closure) {
            /** @var Closure $callable */
            $callable = $callable->bindTo($this->container);
        }

        return $callable;
    }

    /**
     * @param string|callable $toResolve
     * @return string|callable
     */
    private function prepareToResolve($toResolve)
    {
        if (! is_array($toResolve)) {
            return $toResolve;
        }
        $candidate = $toResolve;
        $class = array_shift($candidate);
        $method = array_shift($candidate);
        if (is_string($class) && is_string($method)) {
            return $class . '@' . $method;
        }

        return $toResolve;
    }
}
