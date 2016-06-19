<?php

namespace DTL\PhpTool\Extension;

use PhpBench\DependencyInjection\Container;
use Doctrine\DBAL\Connection;
use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\Console\Application;
use DTL\PhpTool\Console\Command\MigrateCommand;
use Doctrine\DBAL\DriverManager;
use DTL\PhpTool\Scanner\Scanner;
use DTL\PhpTool\Console\Command\ScanCommand;
use DTL\PhpTool\Database\Repository;
use DTL\PhpTool\Console\Command\FindCommand;
use DTL\PhpTool\Refactor\Finder;
use DTL\PhpTool\Refactor\WorkerRegistry;
use DTL\PhpTool\Console\Command\RefactorCommand;
use DTL\PhpTool\Refactor\Worker\PropertyFixerWorker;

class CoreExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'dbal.connection' => [
                'driver' => 'pdo_sqlite',
                'path' => 'phptool.sqlite',
            ],
        ];
    }

    public function load(Container $container)
    {
        $this->loadDbal($container);
        $this->loadRefactoring($container);

        $container->register('phptool.console.application', function ($container) {
            $application = new Application();

            foreach ($container->getServiceIdsForTag('console.command') as $serviceId => $args) {
                $application->add($container->get($serviceId));
            }

            return $application;
        });

        $container->register('phptool.scanner', function ($container) {
            return new Scanner();
        });

        $container->register('phptool.refactor.finder', function ($container) {
            return new Finder(
                $container->get('phptool.repository'),
                $container->get('phptool.scanner')
            );
        });

        $container->register('phptool.repository', function ($container) {
            return new Repository($container->get('dbal.connection'));
        });

        $container->register('phptool.console.command.dbal_migrate', function ($container) {
            return new MigrateCommand($container->get('dbal.connection'));
        }, [ 'console.command' => [] ]);

        $container->register('phptool.console.command.scan', function ($container) {
            return new ScanCommand($container->get('phptool.scanner'), $container->get('phptool.repository'));
        }, [ 'console.command' => [] ]);

        $container->register('phptool.console.command.find', function ($container) {
            return new FindCommand($container->get('phptool.refactor.finder'));
        }, [ 'console.command' => [] ]);

        $container->register('phptool.console.command.refactor', function ($container) {
            return new RefactorCommand($container->get('phptool.refactor.finder'), $container->get('phptool.refactor.worker_registry'));
        }, [ 'console.command' => [] ]);
    }

    public function build(Container $container)
    {
    }

    private function loadDbal(Container $container)
    {
        $container->register('dbal.connection', function ($container) {
            return DriverManager::getConnection($container->getParameter('dbal.connection'));
        });
    }

    private function loadRefactoring(Container $container)
    {
        $container->register('phptool.refactor.worker_registry', function ($container) {
            $registry = new WorkerRegistry($container);

            foreach ($container->getServiceIdsForTag('refactor.worker') as $workerId => $attrs) {
                $registry->register($attrs['name'], $workerId);
            }

            return $registry;
        });

        $container->register('phptool.refactor.worker.property_fixer', function ($container) {
            return new PropertyFixerWorker();
        }, [ 'refactor.worker' => [ 'name' => 'property_fixer' ] ]);
    }
}
