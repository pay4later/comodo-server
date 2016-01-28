<?php

use DI\Container;
use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Set up the application in an anonymous function to avoid defining global variables
 * @return ContainerInterface
 */
return call_user_func(function () {
    $definitions = require __DIR__ . '/../config/global.php';

    if (file_exists(__DIR__ . '/../config/local.php')) {
        $definitions = array_replace_recursive($definitions, require __DIR__ . '/../config/local.php');
    }

    $container = new ContainerBuilder();

    $get = function (Container $c, $class) {
        switch ($class[0]) {
            case '@':
                $class = $c->get(substr($class, 1));
                break;
            case '!':
                $name = substr($class, 1);
                $object = new $name();
                $class = $object;
                break;
        }
        return $class;
    };

    foreach ($definitions['factories'] as $id => $def) {
        if (is_string($def)) {
            $definitions[$id] = DI\get($def);
            continue;
        }

        $factory = isset($def['factory']) ? (array) $def['factory'] : [];
        $factoryClass  = array_shift($factory);
        $factoryMethod = array_shift($factory);
        $arguments = !empty($def['arguments']) ? $def['arguments'] : [];

        if ($factoryClass) { // factory configuration
            $def = function (Container $c) use ($get, $factoryClass, $factoryMethod, $arguments) {
                $factoryClass = $get($c, $factoryClass);
                $factoryMethod = $get($c, $factoryMethod ?: 'create');
                foreach ($arguments as &$argument) {
                    $argument = $get($c, $argument);
                }
                return call_user_func_array([$factoryClass, $factoryMethod], $arguments);
            };
        } else {
            $object = !empty($def['object']) ? $def['object'] : $id;
            $methods = !empty($def['methods']) ? $def['methods'] : [];
            $def = DI\object($object);
            foreach ($methods as $method) {
                $arguments = $method[1];
                $method = $method[0];
                array_unshift($arguments, $method);
                call_user_func_array([$def, 'method'], $arguments);
            }
        }

        $definitions[$id] = $def;
    }

    $container->addDefinitions($definitions);

    return $container->build();
});
