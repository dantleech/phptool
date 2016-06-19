<?php

namespace DTL\PhpTool\Model;

use Doctrine\Common\Util\Inflector;

class ClassReflection
{
    public $path;
    public $name;
    public $namespace;
    public $extends;
    public $isInterface = false;
    public $isAbstract = false;
    public $properties = [];
    public $methods = [];
    public $propertyFetches = [];
    public $interfaces = [];
    public $_stmts = [];

    public function getFqn()
    {
        return $this->namespace ? $this->namespace . '\\' . $this->name : $this->name;
    }

    public function fromArray($array)
    {
        foreach ($array as $key => $value) {
            $property = Inflector::camelize($key);

            if ($property === 'fqn') {
                continue;
            }

            if (!property_exists(get_class($this), $property)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown property "%s"', $property
                ));
            }

            $this->$property = $value;
        }
    }
}
