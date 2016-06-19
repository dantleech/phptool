<?php

namespace DTL\PhpTool\Refactor;

use Interop\Container\ContainerInterface;

class WorkerRegistry
{
    private $workerMap = [];
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function register($name, $serviceId)
    {
        if (isset($this->workerMap[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Worker with name "%s" already registered',
                $name
            ));
        }

        $this->workerMap[$name] = $serviceId;
    }

    public function get($name)
    {
        if (!isset($this->workerMap[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown worker "%s". Known workers: "%s"',
                $name, implode('", "', array_keys($this->workerMap))
            ));
        }

        return $this->container->get($this->workerMap[$name]);
    }
}
