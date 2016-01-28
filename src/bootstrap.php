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

        $factory = (array) $def['factory'];
        $class = array_shift($factory);
        $method = array_shift($factory) ?: 'create';
        $arguments = !empty($def['arguments']) ? $def['arguments'] : [];

        $definitions[$id] = function (Container $c) use ($get, $class, $method, $arguments) {
            $class = $get($c, $class);
            $method = $get($c, $method);
            foreach ($arguments as &$argument) {
                $argument = $get($c, $argument);
            }
            return call_user_func_array([$class, $method], $arguments);
        };
    }

    $container->addDefinitions($definitions);

    return $container->build();
});
