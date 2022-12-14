<?php

namespace Tests\Foundation;

use Gravatalonga\KingFoundation\Kernel;
use PHPUnit\Framework\TestCase;

class ConsoleTest extends TestCase
{
    public function setUp(): void
    {
        $this->console = new Kernel(null, [
            new ConsoleServiceProvider()
        ]);
    }

    /**
     * @test
     */
    public function can_create_console_application ()
    {
    }
}