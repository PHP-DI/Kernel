<?php

namespace DI\Kernel\Test\Fixture;

use RuntimeException;

class FakeComposerLocator extends \ComposerLocator
{
    public static $paths = [];

    public static $rootPath;

    public static function getPath($name)
    {
        $name = strtolower($name);
        if (! isset(self::$paths[$name])) {
            throw new RuntimeException("Composer package not found: {$name}");
        }
        return self::getRootPath() . self::$paths[$name];
    }

    public static function getRootPath()
    {
        return self::$rootPath ?: parent::getRootPath();
    }

    public static function isInstalled($name)
    {
        return isset(self::$paths[$name]);
    }

    public static function getPackages()
    {
        return array_keys(self::$paths);
    }

    public static function getPaths()
    {
        $paths = [];

        foreach (self::$paths as $name => $path) {
            $paths[$name] = self::getRootPath() . $path;
        }

        return $paths;
    }
}
