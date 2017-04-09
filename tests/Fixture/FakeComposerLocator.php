<?php

namespace DI\Kernel\Test\Fixture;

class FakeComposerLocator extends \ComposerLocator
{
    public static $paths = [];

    public static $rootPath;

    public static function getRootPath()
    {
        return self::$rootPath ?: parent::getRootPath();
    }
}
