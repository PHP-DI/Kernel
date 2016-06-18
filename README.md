# PHP-DI application kernel

Kernel for applications built with [PHP-DI](http://php-di.org) and [Puli](http://puli.io) with built-in support for PHP-DI modules.

[![Build Status](https://img.shields.io/travis/PHP-DI/Kernel.svg?style=flat-square)](https://travis-ci.org/PHP-DI/Kernel)
[![Coverage Status](https://img.shields.io/coveralls/PHP-DI/Kernel/master.svg?style=flat-square)](https://coveralls.io/r/PHP-DI/Kernel?branch=master)

## Introduction

TODO

## Installation

```
composer require php-di/kernel
```

Requirements:

- PHP 5.5 or greater
- [Puli CLI tool](http://docs.puli.io/en/latest/installation.html#installing-the-puli-cli)

## Usage

The kernel's role is to create the container. It does so by registering all the configuration files of the modules we ask it to load:

```php
$kernel = new Kernel([
    'twig',
    'doctrine',
    'app',
]);

$container = $kernel->createContainer();
```

### Installing a module

To install a 3rd party module:

- install the package using Composer
- add it to the list of modules your kernel will load, for example:

    ```php
    $kernel = new Kernel([
        'twig',
    ]);
    ```

### Creating a module

1. choose a module name, for example `blogpress`
1. create a resource directory in your package, usually `res/`
1. map it with Puli, for example `puli map /blogpress res`
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
puli.json
```

When users install your package and tell the kernel to load the `blogpress` module, it will load all the files matching the Puli path `/blogpress/config/*.php` (i.e. `vendor/johndoe/blogpress/res/config/*.php` on the filesystem).

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
