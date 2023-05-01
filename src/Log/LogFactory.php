<?php declare(strict_types=1);

namespace Gravatalonga\KingFoundation\Log;

use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ReflectionClass;

/**
 * @method LogFactory handlerStream(mixed $stream, int|string|Level $level = Level::Debug,  bool $bubble = true, ?int $filePermission = null,  bool $useLocking = false, \Closure $formatter = null)
 * @method LogFactory handlerRotatingFile(string $filename, int $maxFiles = 0, \Closure $formatter = null)
 * @method LogFactory handlerGroup(array $array, \Closure $formatter = null)
 * @method LogFactory processUid(int $length = 7)
 * @method LogFactory processMemoryPeakUsage()
 */
final class LogFactory
{
    private ?string $name;

    private array $process = [];

    private array $handler = [];

    private array $formatter = [];

    public function __construct(?string $name)
    {
        $this->name = $name ?? 'logger';
    }

    public static function create(?string $name)
    {
        return new self($name);
    }

    public function build(): LoggerInterface
    {
        $logger =  new Logger($this->name);

        foreach ($this->processes() as $process) {
            $logger->pushProcessor($process);
        }

        foreach ($this->handlers() as $handler) {
            $logger->pushHandler($handler);
        }

        return $logger;
    }

    public function handlers(): array
    {
        return array_map(function ($item) {
            return $item();
        }, array_reverse($this->handler));
    }

    public function processes(): array
    {
        return array_reverse($this->process);
    }

    public function formatters(): array
    {
        return array_reverse($this->formatter);
    }

    public function __call(string $method, array $arguments = [])
    {
        return match ($this->guessCallType($method)) {
            'process' => $this->buildProcess($method, $arguments),
            'handler' => $this->buildHandler($method, $arguments),
            'formatter' => $this->buildFormatter($method, $arguments),
            default => $this,
        };
    }

    private function buildHandler(string $method, array $arguments)
    {
        $contract = str_replace('handler', '', $method);
        $namespace = '\Monolog\Handler\\' . $contract . 'Handler';
        if (! class_exists($namespace)) {
            // todo...
        }

        $formatter = $arguments['formatter'] ?? null;
        if (! $formatter instanceof \Closure && $formatter !== null) {
            // todo...
            throw new \Exception("formatter must be instance of \Closure");
        }

        if ($formatter instanceof \Closure) {
            $formatter = $formatter->bindTo($this);
            unset($arguments['formatter']);
        }

        $r = new ReflectionClass($namespace);
        $handler = $r->newInstanceArgs($arguments);
        $this->handler[] = (function () use ($handler, $formatter) {
            if ($handler instanceof FormattableHandlerInterface && $formatter instanceof \Closure) {
                $formatters = $formatter()->formatters();
                foreach ($formatters as $format) {
                    $handler->setFormatter($format);
                }
            }
            return $handler;
        })->bindTo($this);
        return $this;
    }

    private function buildProcess(string $method, array $arguments)
    {
        $contract = str_replace('process', '', $method);
        $namespace = '\Monolog\Processor\\' . $contract . 'Processor';
        if (! class_exists($namespace)) {
            //
        }
        $r = new ReflectionClass($namespace);
        $process = $r->newInstanceArgs($arguments);
        $this->process[] = $process;
        return $this;
    }

    private function buildFormatter(string $method, array $arguments): self
    {
        $contract = str_replace('formatter', '', $method);
        $namespace = '\Monolog\Formatter\\' . $contract . 'Formatter';
        if (! class_exists($namespace)) {
            //
        }
        $r = new ReflectionClass($namespace);
        $formatter = $r->newInstanceArgs($arguments);
        $this->formatter[] = $formatter;
        return $this;
    }

    private function guessCallType(string $method, array $arguments = [])
    {
        if (str_contains($method, 'process')) {
            return 'process';
        }

        if (str_contains($method, 'handler')) {
            return 'handler';
        }

        if (str_contains($method, 'formatter')) {
            return 'formatter';
        }

        return '';
    }

}