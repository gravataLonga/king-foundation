<?php declare(strict_types=1);

namespace Tests\Foundation\Log;

use Gravatalonga\KingFoundation\Log\LogFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Processor\UidProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LogFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function create_factory()
    {
        /** @var \Monolog\Logger $monologo */
        $monologo = LogFactory::create('application')
            ->handlerStream('file.log', Level::Alert) // {path.storage}/{config.app.name}
            ->handlerRotatingFile('file.log', 10) // {path.storage}/{config.app.name}
            ->processUid(10)
            ->build();

        $this->assertInstanceOf(LoggerInterface::class, $monologo);
        $this->assertCount(1, $monologo->getProcessors());
        $this->assertCount(2, $monologo->getHandlers());
        $this->assertInstanceOf(StreamHandler::class, $monologo->getHandlers()[0]);
        $this->assertInstanceOf(RotatingFileHandler::class, $monologo->getHandlers()[1]);
        $this->assertEquals('application', $monologo->getName());
    }

    /**
     * @test
     */
    public function can_create_handler_only()
    {
        $handlers = LogFactory::create('')
            ->handlerGroup([])
            ->handlers();

        $this->assertIsArray($handlers);
        $this->assertInstanceOf(GroupHandler::class, $handlers[0]);
    }

    /**
     * @test
     */
    public function can_create_processor_only()
    {
        $processes = LogFactory::create('')
            ->processUid()
            ->processes();

        $this->assertIsArray($processes);
        $this->assertInstanceOf(UidProcessor::class, $processes[0]);
    }

    /**
     * @test
     */
    public function can_create_formatter()
    {
        $formatters = LogFactory::create('application')
            ->formatterJson()
            ->formatters();

        $this->assertIsArray($formatters);
        $this->assertCount(1, $formatters);
        $this->assertInstanceOf(FormatterInterface::class, $formatters[0]);
        $this->assertInstanceOf(JsonFormatter::class, $formatters[0]);
    }

    /**
     * @test
     */
    public function can_attach_formatter_to_handler()
    {
        $handlers = LogFactory::create('application')
            ->handlerStream('file.log', formatter: fn () => $this->formatterJson())
            ->handlers();

        $this->assertInstanceOf(JsonFormatter::class, $handlers[0]->getFormatter());
    }
}
