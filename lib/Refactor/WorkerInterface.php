<?php

namespace DTL\PhpTool\Refactor;

use DTL\PhpTool\Model\CompositeClass;
use DTL\PhpTool\Mutator\ClassMutator;

interface WorkerInterface
{
    public function refactor(CompositeClass $class, ClassMutator $mutator);
}
