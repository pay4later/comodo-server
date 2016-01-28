<?php

namespace Pay4Later\Stdlib;

use GeneratedHydrator\Configuration;
use Zend\Stdlib\Hydrator\HydratorInterface;

class HydratorService
{
    private $hydrators = [];

    public function hydrate(array $data, $object)
    {
        return $this->getHydrator(get_class($object))->hydrate($data, $object);
    }

    public function extract($object)
    {
        return $this->getHydrator(get_class($object))->extract($object);
    }

    /**
     * @param string $class
     * @return HydratorInterface
     */
    private function getHydrator($class)
    {
        if (empty($this->hydrators[$class])) {
            $config = new Configuration($class);
            $hydratorClass = $config->createFactory()->getHydratorClass();
            $this->hydrators[$class] = new $hydratorClass();
        }
        return $this->hydrators[$class];
    }
}