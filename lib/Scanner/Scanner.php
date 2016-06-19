<?php

namespace DTL\PhpTool\Scanner;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use DTL\PhpTool\Scanner\Visitor\ClassReader;
use DTL\PhpTool\Model\ClassReflection;

class Scanner
{
    private $parser;

    public function __construct(PhpParser $parser = null)
    {
        $this->parser = $parser ?: (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
    }

    public function scan(\SplFileInfo $file)
    {
        $class = new ClassReflection();

        return $this->scanClass($class, $file);
    }

    public function rescan(ClassReflection $class)
    {
        $this->scanClass($class, $class->path);
    }

    private function scanClass(ClassReflection $class, $file)
    {
        $code = file_get_contents($file);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new ClassReader($class));
        $stmts = $this->parser->parse($code);
        $traverser->traverse($stmts);

        $class->_stmts = $stmts;

        return $class;
    }
}
