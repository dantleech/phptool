<?php

namespace DTL\PhpTool\Database;

use Doctrine\DBAL\Schema\Schema as BaseSchema;

class Schema extends BaseSchema
{
    private $classTable;

    public function __construct()
    {
        parent::__construct();
        $this->createClass();
    }

    private function createClass()
    {
        $table = $this->createTable('class');
        $table->addColumn('fqn', 'string');
        $table->addColumn('path', 'string');
        $table->addColumn('name', 'string');
        $table->addColumn('namespace', 'string', [ 'notnull' => false ]);
        $table->addColumn('extends', 'string', [ 'notnull' => false ]);
        $table->addColumn('is_interface', 'boolean');
        $table->addColumn('is_abstract', 'boolean');
        $table->setPrimaryKey(['fqn']);
        $this->classTable = $table;
    }
}
