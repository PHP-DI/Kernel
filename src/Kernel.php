<?php

namespace DI\Kernel;

use DI\Cache\ArrayCache;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\Cache;
use Interop\Container\ContainerInterface;
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
    public static $puliFactoryClass;

    /**
     * @var string[]
     */
    private $modules;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @param array  $modules     The name of the modules to load.
     * @param string $environment Environment of the application (prod, dev, test, ...).
     */
    public function __construct(array $modules = [], $environment = 'prod')
    {
        $this->modules = $modules;
        $this->environment = $environment;
    }

    /**
     * Add container configuration.
     *
     * Use this method to define config easily when writing a micro-application.
     * In bigger applications you are encouraged to define configuration in
     * files using modules.
     *
     * @see http://php-di.org/doc/php-definitions.html
     */
    public function addConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Configure and create a container using all configuration files registered under
     * the `php-di/configuration` binding type in Puli.
     *
     * @return Container
     */
    public function createContainer()
    {
        if (!self::$puliFactoryClass && !defined('PULI_FACTORY_CLASS')) {
            throw new \RuntimeException('Puli is not installed');
        }

        // Create Puli objects
        $factoryClass = self::$puliFactoryClass ?: PULI_FACTORY_CLASS;
        $puli = new $factoryClass();
        /** @var ResourceRepository $repository */
        $repository = $puli->createRepository();

        $containerBuilder = new ContainerBuilder();

        $cache = $this->getContainerCache();
        if ($cache) {
            $containerBuilder->setDefinitionCache($cache);
        }

        // Puli objects
        $containerBuilder->addDefinitions([
            'puli.factory' => $puli,
            ResourceRepository::class => $repository,
            Discovery::class => function (ContainerInterface $c) {
                $puli = $c->get('puli.factory');
                return $puli->createDiscovery($c->get(ResourceRepository::class));
            },
        ]);

        foreach ($this->modules as $module) {
            $this->loadModule($containerBuilder, $repository, $module);
        }

        if (!empty($this->config)) {
            $containerBuilder->addDefinitions($this->config);
        }

        $this->configureContainerBuilder($containerBuilder);

        return $containerBuilder->build();
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
        foreach ($resources->find('/'.$module.'/config/*.php') as $resource) {
            if ($resource instanceof FilesystemResource) {
                $builder->addDefinitions($resource->getFilesystemPath());
            }
        }

        // Load the environment-specific config if it exists
        $envConfig = '/'.$module.'/config/env/'.$this->environment.'.php';
        if ($resources->contains($envConfig)) {
            $resource = $resources->get($envConfig);
            if ($resource instanceof FilesystemResource) {
                $builder->addDefinitions($resource->getFilesystemPath());
            }
        }
    }
}
