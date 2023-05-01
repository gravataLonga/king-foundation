<?php declare(strict_types=1);

namespace Tests\Foundation;

use Gravatalonga\Container\Container;
use Gravatalonga\Framework\ValueObject\Path;
use Gravatalonga\KingFoundation\TwigServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\ArrayLoader;

class TwigServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function get_entries()
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
    public function can_create_twig_instance()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
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
    public function can_add_filters()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
            'twig.filter' => ['md5'],
            'twig.filter.md5' => function (ContainerInterface $container) {
                return new \Twig\TwigFilter('md5', function ($string) {
                    return md5($string);
                });
            },
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $container->share('twig.loader', function (ContainerInterface $container) {
            return new ArrayLoader([
                'default' => '{{ \'hello\'|md5 }}',
            ]);
        });

        /** @var Environment $twig */
        $twig = $entries[Environment::class]($container);
        $output = $twig->render('default');

        $this->assertNotEmpty($output);
        $this->assertEquals('5d41402abc4b2a76b9719d911017c592', $output);
    }

    /**
     * @test
     */
    public function can_add_globals()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
            'twig.global' => ['text'],
            'twig.global.text' => function (ContainerInterface $container) {
                return new class() {
                    public function hello()
                    {
                        return 'hello world';
                    }
                };
            },
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $container->share('twig.loader', function (ContainerInterface $container) {
            return new ArrayLoader([
                'default' => '{{ text.hello() }}',
            ]);
        });

        /** @var Environment $twig */
        $twig = $entries[Environment::class]($container);
        $output = $twig->render('default');

        $this->assertNotEmpty($output);
        $this->assertEquals('hello world', $output);
    }

    /**
     * @test
     */
    public function can_add_function()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
            'twig.function' => ['text'],
            'twig.function.text' => function (ContainerInterface $container) {
                return new \Twig\TwigFunction('text', function () {
                    return 'hello world';
                });
            },
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $container->share('twig.loader', function (ContainerInterface $container) {
            return new ArrayLoader([
                'default' => '{{ text() }}',
            ]);
        });

        /** @var Environment $twig */
        $twig = $entries[Environment::class]($container);
        $output = $twig->render('default');

        $this->assertNotEmpty($output);
        $this->assertEquals('hello world', $output);
    }

    /**
     * @test
     */
    public function can_add_test()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
            'twig.test' => ['odd'],
            'twig.test.odd' => function (ContainerInterface $container) {
                return new \Twig\TwigTest('odd', function (int $number) {
                    return $number % 2 !== 0;
                });
            },
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $container->share('twig.loader', function (ContainerInterface $container) {
            return new ArrayLoader([
                'default' => '{% if 3 is odd %}number is odd{% endif %}',
            ]);
        });

        /** @var Environment $twig */
        $twig = $entries[Environment::class]($container);
        $output = $twig->render('default');

        $this->assertNotEmpty($output);
        $this->assertEquals('number is odd', $output);
    }

    /**
     * @test
     */
    public function can_add_extensions()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
            'twig.extension' => ['simple'],
            'twig.extension.simple' => function (ContainerInterface $container) {
                return new class() extends AbstractExtension {
                    public function getFilters()
                    {
                        return [
                            new \Twig\TwigFilter('rot13', 'str_rot13'),
                        ];
                    }
                };
            },
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();
        $container->share('twig.loader', function (ContainerInterface $container) {
            return new ArrayLoader([
                'default' => '{{ \'hello\'|rot13 }}',
            ]);
        });

        /** @var Environment $twig */
        $twig = $entries[Environment::class]($container);
        $output = $twig->render('default');

        $this->assertNotEmpty($output);
        $this->assertEquals('uryyb', $output);
    }

    /**
     * @test
     */
    public function get_empty_array_on_each_plugin_section()
    {
        $container = new Container([
            'path.resource' => new Path('./tests/stub'),
        ]);
        $provider = new TwigServiceProvider();
        $entries = $provider->factories();

        $this->assertIsArray($entries['twig.filter']($container));
        $this->assertEmpty($entries['twig.filter']($container));
        $this->assertIsArray($entries['twig.global']($container));
        $this->assertEmpty($entries['twig.global']($container));
        $this->assertIsArray($entries['twig.function']($container));
        $this->assertEmpty($entries['twig.function']($container));
        $this->assertIsArray($entries['twig.function']($container));
        $this->assertEmpty($entries['twig.function']($container));
        $this->assertIsArray($entries['twig.extension']($container));
        $this->assertEmpty($entries['twig.extension']($container));
    }
}
