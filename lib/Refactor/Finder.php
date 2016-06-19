<?php

namespace DTL\PhpTool\Refactor;

use DTL\PhpTool\Database\Repository;
use DTL\PhpTool\Scanner\Scanner;
use DTL\PhpTool\PhpTool;

class Finder
{
    private $repository;
    private $scanner;

    public function __construct(Repository $repository, Scanner $scanner)
    {
        $this->repository = $repository;
        $this->scanner = $scanner;
    }

    public function find($classOrPath)
    {
        $filepath = PhpTool::normalizePath($classOrPath);

        if (file_exists($filepath)) {
            $composite = $this->repository->findFile($filepath);
        } else {
            $composite = $this->repository->findClass($classOrPath);
        }

        foreach ($composite->getClasses() as $class) {
            $this->scanner->rescan($class);
        }

        return $composite;
    }
}
