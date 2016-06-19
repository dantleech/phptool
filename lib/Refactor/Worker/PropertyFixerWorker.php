<?php

namespace DTL\PhpTool\Refactor\Worker;

use DTL\PhpTool\Refactor\WorkerInterface;
use DTL\PhpTool\Model\CompositeClass;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\PrettyPrinter;
use PHPParserPSR2_Printer;
use DTL\PhpTool\Mutator\ClassMutator;

class PropertyFixerWorker implements WorkerInterface
{
    public function refactor(CompositeClass $composite, ClassMutator $mutator)
    {
        $undeclared = array_diff(
            $composite->getTop()->propertyFetches, 
            array_keys($composite->getAccessibleProperties())
        );

        foreach ($undeclared as $property) {
            $mutator->addProperty($property, 'private');
        }
    }
}

class PropertyFixerVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_) {
            array_unshift($node->stmts, new Stmt\Property(
                Class_::MODIFIER_PRIVATE,
                [
                    new Stmt\PropertyProperty('barbar')
                ]
            ));
        }
    }
}
