<?php

namespace DTL\PhpTool;

use PhpBench\DependencyInjection\Container;
use DTL\PhpTool\Extension\CoreExtension;

class PhpTool
{
    public static function run()
    {
        $container = new Container([
            CoreExtension::class
        ]);
        $container->init();
        $application = $container->get('phptool.console.application');
        $application->run();
    }

    /**
     * If the path is relative we need to use the current working path
     * because otherwise it will be the script path, which is wrong in the
     * context of a PHAR.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath($path)
    {
        if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
            return $path;
        }

        return getcwd() . DIRECTORY_SEPARATOR . $path;
    }

}
