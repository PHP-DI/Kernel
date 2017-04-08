<?php

namespace DI\Kernel;

use ComposerLocator;
use DI\Cache\ArrayCache;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\Cache;

/**
 * Application kernel.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Kernel
{
    /**
     * @var string
     */
    public static $locatorClass = 'ComposerLocator';

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
    public function __construct(array $modules = [], string $environment = 'prod')
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
     * Configure and create a container using all configuration files found in included modules.
     */
    public function createContainer() : Container
    {
        $containerBuilder = new ContainerBuilder();

        $cache = $this->getContainerCache();
        if ($cache) {
            $containerBuilder->setDefinitionCache($cache);
        }

        foreach ($this->modules as $module) {
            $this->loadModule($containerBuilder, $module);
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

    private function loadModule(ContainerBuilder $builder, string $module)
    {
        $locatorClass = self::$locatorClass;
        $path = $locatorClass::getPath($module);

        // Load all config files in the config/ directory
        foreach (glob($path.'/res/config/*.php') as $file) {
            $builder->addDefinitions($file);
        }

        // Load the environment-specific config if it exists
        $envConfig = $path.'/res/config/env/'.$this->environment.'.php';
        if (file_exists($envConfig)) {
            $builder->addDefinitions($envConfig);
        }
    }
}
