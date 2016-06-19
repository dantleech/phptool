<?php

namespace DTL\PhpTool\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DTL\PhpTool\PhpTool;
use DTL\PhpTool\Findner\Findner;
use Symfony\Component\Console\Input\InputArgument;
use DTL\PhpTool\Database\Repository;
use Symfony\Component\Console\Helper\Table;
use DTL\PhpTool\Scanner\Scanner;
use DTL\PhpTool\Refactor\Finder;
use DTL\PhpTool\Refactor\WorkerRegistry;
use DTL\PhpTool\Mutator\ClassMutator;

class RefactorCommand extends Command
{
    private $finder;
    private $registry;

    public function __construct(Finder $finder, WorkerRegistry $registry)
    {
        parent::__construct();
        $this->finder = $finder;
        $this->registry = $registry;
    }

    public function configure()
    {
        $this->setName('refactor');
        $this->setDescription('Apply a refactoring by file or class');
        $this->addArgument('classpath', InputArgument::REQUIRED);
        $this->addOption('worker', null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Name of worker to apply');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('classpath');
        $composite = $this->finder->find($class);
        $mutator = new ClassMutator($composite->getTop());

        foreach ($input->getOption('worker') as $workerName) {
            $worker = $this->registry->get($workerName);
            $worker->refactor($composite, $mutator);
        }

        $output->writeln($composite->getTop()->path);
        $output->write(PHP_EOL);
        $changes = $mutator->getChanges();

        $output->writeln(sprintf('%s changes', count($changes)));

        foreach ($changes as $change) {
            $output->writeln(sprintf(
                '  [%s] %s', $change->getType(), $change->getChange()
            ));
        }

        if (false === $input->getOption('dry-run')) {
            file_put_contents($composite->getTop()->path, $mutator->getContents());
        }
    }
}

