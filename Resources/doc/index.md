Getting Started With Sonatra SessionBundle
==========================================

The Symfony2 session can be stored in a database using PDO, 
and this bundle facilitates the configuration and initialization of the 
sessions in the main database (or another).

## Prerequisites

This version of the bundle requires Symfony 2.1+.

## Installation

Installation is a quick, 2 step process:

1. Download Sonatra SessionBundle using composer
2. Enable the bundle
3. Configure the bundle (optionnal)

### Step 1: Download Sonatra SessionBundle using composer

Add Sonatra SessionBundle in your composer.json:

``` js
{
    "require": {
        "sonatra/session-bundle": "~1.0"
    }
}
```

Or tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update sonatra/session-bundle
```

Composer will install the bundle to your project's `vendor/sonatra` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Sonatra\Bundle\SessionBundle\SonatraSessionBundle(),
    );
}
```

### Step 3: Configure the bundle (optionnal)

You can override the default configuration adding `sonatra_session` tree in `app/config/config.yml`.
For see the reference of Sonatra Session Configuration, execute command:

``` bash
$ php app/console config:dump-reference SonatraSessionBundle 
```

### Next Steps

Now that you have completed the basic installation and configuration of the
Sonatra SessionBundle, you are ready to learn about usages of the bundle.

The following documents are available:

- [Command Initalization](command_initialization.md)
