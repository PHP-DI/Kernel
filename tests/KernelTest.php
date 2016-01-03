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

/**
 * @covers \DI\Application\Kernel
 */
class KernelTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!defined('PULI_FACTORY_CLASS')) {
            define('PULI_FACTORY_CLASS', PuliFactoryClass::class);
        }

        PuliFactoryClass::$repository = new InMemoryRepository();
        PuliFactoryClass::$discovery = new InMemoryDiscovery();
    }

    /**
     * @test
     */
    public function creates_a_container()
    {
        $this->assertInstanceOf('DI\Container', (new Kernel())->createContainer());
    }

    /**
     * @test
     */
    public function registers_puli_repository()
    {
        $container = (new Kernel())->createContainer();
        $this->assertInstanceOf(ResourceRepository::class, $container->get(ResourceRepository::class));
    }

    /**
     * @test
     */
    public function registers_puli_discovery()
    {
        $container = (new Kernel())->createContainer();
        $this->assertInstanceOf(Discovery::class, $container->get(Discovery::class));
    }

    /**
     * @test
     */
    public function registers_module_configuration_files()
    {
        $this->createPuliResource('/blog/config.php', __DIR__.'/Fixture/config.php');
        $this->bindPuliResource('/blog/config.php', Kernel::PULI_BINDING_NAME);

        $container = (new Kernel())->createContainer();
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
