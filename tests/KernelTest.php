<?php

namespace DI\Kernel\Test;

use DI\Container;
use DI\Kernel\Kernel;
use DI\Kernel\Test\Fixture\FakeComposerLocator;
use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase
{
    /**
     * @var Kernel
     */
    private $kernel;

    public function setUp()
    {
        // Mock the Composer locator
        Kernel::$locatorClass = FakeComposerLocator::class;
        FakeComposerLocator::$rootPath = __DIR__;

        $this->kernel = new Kernel();
    }

    /** @test */
    public function creates_a_container()
    {
        self::assertInstanceOf(Container::class, $this->kernel->createContainer());
    }

    /** @test */
    public function loads_module_configs()
    {
        FakeComposerLocator::$paths = [
            'php-di/blog' => '/test-module',
        ];

        $this->kernel = new Kernel([
            'php-di/blog',
        ]);
        $container = $this->kernel->createContainer();

        $this->assertEquals('bar', $container->get('foo'));
    }

    /** @test */
    public function loads_module_environment_config()
    {
        FakeComposerLocator::$paths = [
            'php-di/blog' => '/test-module',
        ];

        $this->kernel = new Kernel([
            'php-di/blog',
        ], 'dev');
        $container = $this->kernel->createContainer();

        $this->assertEquals('biz', $container->get('foo'));
    }

    /** @test */
    public function uses_provided_config()
    {
        $this->kernel = new Kernel();
        $this->kernel->addConfig([
            'foo' => 'bar',
            'bar' => 'bar',
        ]);
        $this->kernel->addConfig([
            'foo' => 'biz',
        ]);
        $container = $this->kernel->createContainer();

        $this->assertEquals('biz', $container->get('foo'));
        $this->assertEquals('bar', $container->get('bar'));
    }

    /** @test */
    public function exposes_the_environment()
    {
        $this->kernel = new Kernel;

        $this->assertEquals('prod', $this->kernel->getEnvironment());
    }
}
