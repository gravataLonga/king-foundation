<?php

namespace Tests\Foundation;

use Gravatalonga\KingFoundation\SlimServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;

/**
 * @covers \Gravatalonga\KingFoundation\SlimServiceProvider
 */
class SlimServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries_from_service_provider()
    {
        $service = new SlimServiceProvider();
        $entries = $service->factories();

        $this->assertArrayHasKey(ResponseFactoryInterface::class, $entries);
        $this->assertArrayHasKey(CallableResolverInterface::class, $entries);
        $this->assertArrayHasKey(RouteCollectorInterface::class, $entries);
    }
}
