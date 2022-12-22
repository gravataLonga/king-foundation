<?php

namespace Tests\Foundation;

use Gravatalonga\Container\Container;
use Gravatalonga\Framework\ValueObject\Path;
use Gravatalonga\KingFoundation\TwigServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class TwigServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries ()
    {
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $extensions = $provider->extensions();

        $this->assertEmpty($extensions);
        $this->assertNotEmpty($entries);
        $this->assertArrayHasKey('twig.filter', $entries);
        $this->assertArrayHasKey('twig.global', $entries);
        $this->assertArrayHasKey('twig.function', $entries);
        $this->assertArrayHasKey('twig.test', $entries);
        $this->assertArrayHasKey('twig.extension', $entries);
        $this->assertArrayHasKey('twig.loader', $entries);
        $this->assertArrayHasKey('twig.options', $entries);
        $this->assertArrayHasKey('twig', $entries);
        $this->assertArrayHasKey(Environment::class, $entries);
    }

    /**
     * @test
     */
    public function can_create_twig_instance ()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub')
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $container->share('twig.loader', $entries['twig.loader']($container));

        $twig = $entries[Environment::class]($container);

        $this->assertInstanceOf(Environment::class, $twig);
    }

    /**
     * @test
     */
    public function can_add_filters ()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
            'twig.filter' => ['md5'],
            'twig.filter.md5' => function(ContainerInterface $container) {
                return new \Twig\TwigFilter('md5', function ($string) {
                    return md5($string);
                });
            }
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $container->share('twig.loader', function (ContainerInterface $container) {
            return new ArrayLoader([
                'default' => '{{ \'hello\'|md5 }}'
            ]);
        });

        /** @var Environment $twig */
        $twig = $entries[Environment::class]($container);
        $output = $twig->render('default');

        $this->assertNotEmpty($output);
        $this->assertEquals('5d41402abc4b2a76b9719d911017c592', $output);
    }
}