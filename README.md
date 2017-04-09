# PHP-DI application kernel

Kernel for building modules with [PHP-DI](http://php-di.org).

[![Build Status](https://img.shields.io/travis/PHP-DI/Kernel.svg?style=flat-square)](https://travis-ci.org/PHP-DI/Kernel)
[![Coverage Status](https://img.shields.io/coveralls/PHP-DI/Kernel/master.svg?style=flat-square)](https://coveralls.io/r/PHP-DI/Kernel?branch=master)

## Introduction

The Kernel let's you build an application based on PHP-DI modules.

## Installation

```
composer require php-di/kernel
```

## Usage

The kernel's role is to create the container. It does so by registering all the configuration files of the modules we ask it to load:

```php
$kernel = new Kernel([
    'twig/twig',
    'doctrine/dbal',
    'vendor/app',
]);

$container = $kernel->createContainer();
```

If you want to register configuration on the container, you can:

- create a module - this is the recommended solution, read the next sections to learn more
- or set the configuration directly - this is useful in micro-frameworks or micro-applications:

    ```php
    $kernel = new Kernel();
    $kernel->addConfig([
        'db.host' => 'localhost',
    ]);
    ```

### Installing a module

To install a 3rd party module:

- install the package using Composer
- add it to the list of modules your kernel will load, for example:

    ```php
    $kernel = new Kernel([
        'twig/twig',
    ]);
    ```

### Creating a module

1. the Composer package name is the module name
1. create a resource directory in your package, usually `res/`
1. create as many PHP-DI configuration files as needed in `res/config/`

That's it. Here is what your package should look like:

```
res/
    config/
        config.php
    ...
src/
    ...
composer.json
```

When the module is registered in the kernel like this:

```php
$kernel = new Kernel([
    'foo/bar',
]);
```

all the files in `vendor/foo/bar/res/config/*.php` will be loaded.

**Your main application will probably contain configuration files too: it is also a module**. Since it may not have a package name in `composer.json` you will need to set one. You can name it `app`, for example:

```json
{
    "name": "app",
    "require": {
        // ...
    }
}
```

That way you can let the kernel load your application as a module:

```php
$kernel = new Kernel([
    'app',
]);
```

### Environments

Applications often need to behave differently according to the environment: `dev`, `prod`, etc.

PHP-DI's Kernel let you write config for specific environments through a simple convention:

```
res/
    config/
        config.php
        env/
            dev.php
            prod.php
    ...
```

You can then instruct the environment to load:

```php
$kernel = new Kernel($modules, 'dev'); // dev environment
$kernel = new Kernel($modules, 'prod'); // prod environment
```

Note that **environments are optional**.
