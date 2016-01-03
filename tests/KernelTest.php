<?php

namespace DI\Kernel\Test;

use DI\Kernel\Kernel;
use DI\Kernel\Test\Fixture\PuliFactoryClass;
use Puli\Discovery\Api\Discovery;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
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

        $this->kernel = new Kernel();
        // Mock the Puli factory
        $this->kernel->setPuliFactoryClass(PuliFactoryClass::class);
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
    public function registers_module_configuration_files()
    {
        $this->createPuliResource('/blog/config.php', __DIR__.'/Fixture/config.php');
        $this->bindPuliResource('/blog/config.php', Kernel::PULI_BINDING_NAME);

        $container = $this->kernel->createContainer();
        $this->assertEquals('bar', $container->get('foo'));
    }

    private function createPuliResource($path, $file)
    {
        PuliFactoryClass::$repository->add($path, new FileResource($file));
    }

    private function bindPuliResource($path, $bindingName)
    {
        PuliFactoryClass::$discovery->addBindingType(new BindingType($bindingName));

        $binding = new ResourceBinding($path, $bindingName);
        $binding->setRepository(PuliFactoryClass::$repository);
        PuliFactoryClass::$discovery->addBinding($binding);
    }
}
