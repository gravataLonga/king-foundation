<?php

namespace Tests\Testing;

use Gravatalonga\KingFoundation\Testing\TraitRequest;
use PHPUnit\Framework\TestCase;

class TraitRequestTest extends TestCase
{
    public function getTraitRequest(): object
    {
        return new class() {
            use TraitRequest;
        };
    }

    /**
     * @test
     */
    public function can_create_request()
    {
        $trait = $this->getTraitRequest();

        /** @var \Slim\Psr7\Request $request */
        $request = $trait->createRequest('GET', '/ping', 'payload=1', ['Content-Type' => 'application/json']);

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('//:80/ping', (string)$request->getUri());
        $this->assertNotEmpty($request->getHeaders());
        $this->assertEquals(['Content-Type', 'Host'], array_keys($request->getHeaders()));
        $this->assertEquals('payload=1', (string)$request->getBody());
    }
}
