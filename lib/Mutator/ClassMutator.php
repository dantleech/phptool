<?php

namespace DTL\PhpTool\Mutator;

use PhpParser\Node\Stmt;
use DTL\PhpTool\Model\ClassReflection;

class ClassMutator
{
    private $index;
    private $lines;
    private $stmts = [];
    private $class;
    private $padding;
    private $changes = [];

    public function __construct(ClassReflection $class)
    {
        $this->class = $class;
        $this->flattenize($class->_stmts);

        $file = file_get_contents($class->path);
        $this->lines = explode("\n", $file);
    }

    public function addProperty($name, $access = 'public', $default = null)
    {
        $this->findLine(Stmt\Class_::class);
        $this->nextLine();
        $this->addLine(sprintf('%s $%s;', $access, $name));
    }

    public function getContents()
    {
        return implode("\n", $this->lines);
    }

    public function getChanges()
    {
        return $this->changes;
    }

    private function addLine($line)
    {
        $start = array_slice($this->lines, 0, $this->index);
        $start[] = $this->padLine($line);
        $end = array_slice($this->lines, $this->index);
        $this->changes[] = new Change('+', $line);
        $this->lines = array_merge($start, $end);
    }

    private function nextLine()
    {
        $this->index++;
        return $this->getLine();
    }

    private function getLine()
    {
        return $this->lines[$this->index];
    }

    private function findLine($class)
    {
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof $class) {
                return $this->index = $stmt->getAttribute('startLine');
            }
        }
    }

    private function flattenize(array $stmts)
    {
        foreach ($stmts as $stmt) {
            $this->stmts[] = $stmt;

            if (isset($stmt->stmts)) {
                $this->flattenize($stmt->stmts);
            }
        }
    }

    private function padLine($line)
    {
        if (null === $this->padding) {
            $index = $this->index;

            while (isset($this->lines[$index])) {
                $cline = $this->lines[$index];
                if (trim($cline) !== '') {
                    $padding = preg_match('{^( *).*$}', $cline, $matches);
                    if ($matches) {
                        $this->padding = $matches[1];
                        return $this->padLine($line);
                    }
                }
            }

            $this->padding = str_repeat(' ', 4);
        }

        return $this->padding . $line;
    }
}
