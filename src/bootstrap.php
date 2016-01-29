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

        if (isset($def['factory'])) { // factory configuration
            $factory = $def['factory'];
            $object = $factory['object'];
            $method = isset($factory['method']) ? $factory['method'] : 'create';
            $params = isset($factory['params']) ? $factory['params'] : [];

            $def = function (Container $c) use ($get, $object, $method, $params) {
                $object = $get($c, $object);
                $method = $get($c, $method);
                foreach ($params as &$param) {
                    $param = $get($c, $param);
                }
                return call_user_func_array([$object, $method], $params);
            };
        } else {
            $object = !empty($def['object']) ? $def['object'] : $id;
            $methods = !empty($def['methods']) ? $def['methods'] : [];
            $def = DI\object($object);
            foreach ($methods as $method) {
                $params = $method[1];
                $method = $method[0];
                array_unshift($params, $method);
                call_user_func_array([$def, 'method'], $params);
            }
        }

        $definitions[$id] = $def;
    }

    $container->addDefinitions($definitions);

    return $container->build();
});
