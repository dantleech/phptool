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

class FindCommand extends Command
{
    private $finder;

    public function __construct(Finder $finder)
    {
        parent::__construct();
        $this->finder = $finder;
    }

    public function configure()
    {
        $this->setName('find');
        $this->setDescription('Locate class information by class name');
        $this->setHelp(<<<'EOT'
EOT
        );
        $this->addArgument('classpath', InputArgument::REQUIRED);
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Output as JSON');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('classpath');
        $composite = $this->finder->find($class);
        $merged = $composite->getMergedClass();

        if ($input->getOption('json')) {
            $output->write(json_encode($merged, JSON_PRETTY_PRINT));
            return 0;
        }

        $table = new Table($output);

        $table->addRow([ 'path', $merged->path ]);
        $table->addRow([ 'fqn', $merged->getFqn() ]);
        $table->addRow([ 'extends', $merged->extends ]);
        $table->addRow([ 'interfaces', implode(', ', $merged->interfaces) ]);
        $table->addRow([ 'property fetches', implode(', ', $merged->propertyFetches) ]);
        $table->addRow([ 'properties', implode(', ', array_keys($merged->properties)) ]);
        $table->addRow([ 'methods', implode(', ', array_keys($merged->methods)) ]);

        $table->render();
    }
}
