<?php

namespace DI\Kernel\Test;

use DI\Kernel\Kernel;
use DI\Kernel\Test\Fixture\PuliFactoryClass;
use Puli\Discovery\Api\Discovery;
use Puli\Discovery\InMemoryDiscovery;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\InMemoryRepository;
use Puli\Repository\Resource\FileResource;

class KernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Kernel
     */
    private $kernel;

    public function setUp()
    {
        PuliFactoryClass::$repository = new InMemoryRepository();
        PuliFactoryClass::$discovery = new InMemoryDiscovery();

        // Mock the Puli factory
        Kernel::$puliFactoryClass = PuliFactoryClass::class;

        $this->kernel = new Kernel();
    }

    /**
     * @test
     */
    public function creates_a_container()
    {
        $this->assertInstanceOf('DI\Container', $this->kernel->createContainer());
    }

    /**
     * @test
     */
    public function registers_puli_repository()
    {
        $container = $this->kernel->createContainer();
        $this->assertInstanceOf(ResourceRepository::class, $container->get(ResourceRepository::class));
    }

    /**
     * @test
     */
    public function registers_puli_discovery()
    {
        $container = $this->kernel->createContainer();
        $this->assertInstanceOf(Discovery::class, $container->get(Discovery::class));
    }

    /**
     * @test
     */
    public function loads_module_configs()
    {
        PuliFactoryClass::$repository->add('/blog/config/config.php', new FileResource(__DIR__.'/test-module/config.php'));

        $this->kernel = new Kernel([
            'blog',
        ]);
        $container = $this->kernel->createContainer();

        $this->assertEquals('bar', $container->get('foo'));
    }

    /**
     * @test
     */
    public function loads_module_environment_config()
    {
        PuliFactoryClass::$repository->add('/blog/config/config.php', new FileResource(__DIR__.'/test-module/config.php'));
        PuliFactoryClass::$repository->add('/blog/config/env/dev.php', new FileResource(__DIR__.'/test-module/env/dev.php'));

        $this->kernel = new Kernel([
            'blog',
        ], 'dev');
        $container = $this->kernel->createContainer();

        $this->assertEquals('biz', $container->get('foo'));
    }

    /**
     * @test
     */
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
}
