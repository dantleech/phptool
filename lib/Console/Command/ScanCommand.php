<?php

namespace DTL\PhpTool\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use DTL\PhpTool\PhpTool;
use DTL\PhpTool\Scanner\Scanner;
use Symfony\Component\Console\Input\InputArgument;
use DTL\PhpTool\Database\Repository;

class ScanCommand extends Command
{
    private $scanner;
    private $repository;

    public function __construct(Scanner $scanner, Repository $repository)
    {
        parent::__construct();
        $this->scanner = $scanner;
        $this->repository = $repository;
    }

    public function configure()
    {
        $this->setName('scan');
        $this->setDescription('Scan a file or directory');
        $this->setHelp(<<<'EOT'
EOT
        );
        $this->addArgument('path', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        $finder = new Finder();
        $path = PhpTool::normalizePath($path);

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist (cwd: %s)',
                $path,
                getcwd()
            ));
        }

        if (is_dir($path)) {
            $finder->in($path)
                ->name('*.php');
        } else {
            // the path is already a file, just restrict the finder to that.
            $finder->in(dirname($path))
                ->depth(0)
                ->name(basename($path));
        }

        foreach ($finder as $file) {
            $output->writeln(str_replace(getcwd(), '.', $file->getPathname()));

            try {
                $class = $this->scanner->scan($file);
            } catch (\PhpParser\Error $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                continue;
            }

            if (null == $class->name) {
                continue;
            }

            $class->path = $file->getPathname();
            $this->repository->storeClass($class);
        }
    }
}


