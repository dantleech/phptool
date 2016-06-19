<?php

namespace DTL\PhpTool\Model;

use DTL\PhpTool\Model\ClassReflection;

class CompositeClass extends ClassReflection
{
    private $classes = [];
    private $top = null;

    public function __construct(array $classes)
    {
        foreach ($classes as $class) {
            $this->addClass($class);
        }
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function getMergedClass()
    {
        $mergedClass = new ClassReflection();

        foreach (array_reverse($this->classes) as $class) {
            foreach ($class as $property => $value) {
                if (is_array($value)) {
                    $mergedClass->$property = array_merge(
                        $mergedClass->$property,
                        $value
                    );
                    ksort($mergedClass->$property);
                    continue;
                }

                $mergedClass->$property = $value;
            }
        }

        return $mergedClass;
    }

    public function getTop()
    {
        return $this->top;
    }

    public function getAccessibleProperties()
    {
        $properties = [];
        foreach (array_reverse($this->classes) as $class) {
            foreach ($class->properties as $property) {
                if ($class !== $this->top && $property->access === 'private') {
                    continue;
                }

                $properties[$property->name] = $property;
            }
        }

        return $properties;
    }

    private function addClass(ClassReflection $class)
    {
        if (null === $this->top) {
            $this->top = $class;
        }
        $this->classes[$class->getFqn()] = $class;
    }
}
