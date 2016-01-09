<?php

namespace DI\Kernel;

use DI\Cache\ArrayCache;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\Cache;
use Puli\Discovery\Api\Discovery;
use Puli\Repository\Api\Resource\FilesystemResource;
use Puli\Repository\Api\ResourceRepository;

/**
 * Application kernel.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Kernel
{
    /**
     * If null, defaults to the constant PULI_FACTORY_CLASS defined by Puli.
     *
     * @var string|null
     */
    private $puliFactoryClass;

    /**
     * @var string[]
     */
    private $modules;

    /**
     * @param array $modules The name of the modules to load.
     */
    public function __construct(array $modules = [])
    {
        $this->modules = $modules;
    }

    /**
     * Configure and create a container using all configuration files registered under
     * the `php-di/configuration` binding type in Puli.
     *
     * @return Container
     */
    public function createContainer()
    {
        if (!$this->puliFactoryClass && !defined('PULI_FACTORY_CLASS')) {
            throw new \RuntimeException('Puli is not installed');
        }

        // Create Puli objects
        $factoryClass = $this->puliFactoryClass ?: PULI_FACTORY_CLASS;
        $factory = new $factoryClass();
        /** @var ResourceRepository $repository */
        $repository = $factory->createRepository();

        $containerBuilder = new ContainerBuilder();

        $cache = $this->getContainerCache();
        if ($cache) {
            $containerBuilder->setDefinitionCache($cache);
        }

        // Puli objects
        $containerBuilder->addDefinitions([
            ResourceRepository::class => $repository,
            Discovery::class => function () use ($factory, $repository) {
                return $factory->createDiscovery($repository);
            },
        ]);

        foreach ($this->modules as $module) {
            $this->loadModule($containerBuilder, $repository, $module);
        }

        $this->configureContainerBuilder($containerBuilder);

        return $containerBuilder->build();
    }

    /**
     * @param string $class
     */
    public function setPuliFactoryClass($class)
    {
        $this->puliFactoryClass = $class;
    }

    /**
     * Override this method to configure the cache to use for container definitions.
     *
     * @return Cache|null
     */
    protected function getContainerCache()
    {
        return new ArrayCache();
    }

    /**
     * Override this method to customize the container builder before it is used.
     */
    protected function configureContainerBuilder(ContainerBuilder $containerBuilder)
    {
    }

    private function loadModule(ContainerBuilder $builder, ResourceRepository $resources, $module)
    {
        // Load all config files in the config/ directory
        foreach ($resources->find('/' . $module . '/config/*.php') as $resource) {
            if ($resource instanceof FilesystemResource) {
                $builder->addDefinitions($resource->getFilesystemPath());
            }
        }
    }
}
