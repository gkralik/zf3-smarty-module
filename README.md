# zf3-smarty-module

This is a module for integrating the [Smarty](http://www.smarty.net) template engine with [Laminas](https://getlaminas.org/) or [Zend Framework 3](http://framework.zend.com).

Version 2.x supports [Laminas](https://getlaminas.org/).

Version 1.x supports [Zend Framework 3](http://framework.zend.com). See branch [1.x](https://github.com/gkralik/zf3-smarty-module/tree/1.x)

## Installation with Composer

Installing via [Composer](http://getcomposer.org) is the only supported method.

```shell script
composer require gkralik/zf3-smarty-module:2.0.0
```

## Configuration

For information on supported options refer to the [module config file](https://github.com/gkralik/zf3-smarty-module/tree/master/config/module.config.php).

There is also a [sample configuration file](https://github.com/gkralik/zf3-smarty-module/tree/master/config/zf3-smarty-module.config.php.dist) with all available configuration options.

You can set options for the Smarty engine under the `smarty_options` configuration key (eg `force_compile`, etc).

Pay attention to the `compile_dir` and `cache_dir` keys. Smarty needs write access to the directories specified there.

## Documentation

### Using Zend Framework view helpers

Using view helpers of ZF3 is supported. Just call the view helper as you would do in a PHTML template:

```smarty
{$this->doctype()}

{$this->basePath('some/path')}
```
