<?php

namespace DTL\PhpTool\Scanner\Visitor;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use DTL\PhpTool\Model\ClassReflection;
use DTL\PhpTool\Model\PropertyReflection;
use PhpParser\Node\Expr;
use DTL\PhpTool\Model\MethodReflection;

class ClassReader extends NodeVisitorAbstract
{
    private $class;

    public function __construct(ClassReflection $class)
    {
        $this->class = $class;
    }

    public function enterNode(Node $node) 
    {
        if ($node instanceof Stmt\Namespace_) {
            $this->class->namespace = $node->name->toString();
        }

        if ($node instanceof Stmt\Class_) {
            $this->class->name = $node->name;

            if ($node->extends) {
                $this->class->extends = $node->extends->toString();
            }

            if ($node->implements) {
                foreach ($node->implements as $interface) {
                    $this->class->interfaces[] = $interface->toString();
                }
            }
        }

        if ($node instanceof Stmt\Property) {
            $access = $node->isProtected() ? 'protected' : $node->isPrivate() ? 'private' : 'public';
            $static = $node->isStatic();
            foreach ($node->props as $prop) {
                $property = new PropertyReflection();
                $property->name = $prop->name;
                $property->access = $access;
                $property->static = $static;
                $this->class->properties[$prop->name] = $property;
            }
        }

        if ($node instanceof Stmt\ClassMethod) {
            $access = $node->isProtected() ? 'protected' : $node->isPrivate() ? 'private' : 'public';
            $static = $node->isStatic();
            $property = new MethodReflection();
            $property->name = $node->name;
            $property->access = $access;
            $property->static = $static;
            $this->class->methods[$node->name] = $property;
        }

        if ($node instanceof Stmt\Interface_) {
            $this->class->name = $node->name;
            $this->class->isInterface = true;
        }

        if ($node instanceof Expr\PropertyFetch) {
            if (false === is_object($node->name)) {
                $this->class->propertyFetches[$node->name] = $node->name;
            }
        }
    }
}

