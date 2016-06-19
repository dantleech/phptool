<?php

namespace DTL\PhpTool\Database;

use Doctrine\DBAL\Connection;
use DTL\PhpTool\Model\ClassReflection;
use DTL\PhpTool\Model\CompositeClass;

class Repository
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function storeClass(ClassReflection $class)
    {
        $data = [
            'path' => $class->path,
            'fqn' => $class->getFqn(),
            'name' => $class->name,
            'namespace' => $class->namespace,
            'is_interface' => $class->isInterface,
            'is_abstract' => $class->isAbstract,
            'extends' => $class->extends
        ];

        $this->insertUpdate('class', $data);
    }

    public function findClass($classFqn)
    {
        $stmt = $this->connection->prepare('SELECT * FROM class WHERE fqn = :fqn');
        $stmt->execute([
            'fqn' => $classFqn,
        ]);
        $res = $stmt->fetch();

        if (false === $res) {
            throw new \InvalidArgumentException(sprintf(
                'Class not found "%s"',
                $classFqn
            ));
        }

        return $this->hydrateClass($res);
    }

    public function findFile($path)
    {
        $stmt = $this->connection->prepare('SELECT * FROM class WHERE path = :path');
        $stmt->execute([
            'path' => $path,
        ]);
        $res = $stmt->fetch();

        if (false === $res) {
            throw new \InvalidArgumentException(sprintf(
                'File not found "%s" in database, maybe rescan?',
                $path
            ));
        }

        return $this->hydrateClass($res);
    }

    private function hydrateClass(array $res)
    {
        $class = new ClassReflection();
        $class->fromArray($res);
        $classes = [ $class ];

        while ($class->extends) {
            $classes[] = $class = $this->findClass($class->extends)->getTop();
        }

        $compositeClass = new CompositeClass($classes);

        return $compositeClass;
    }

    private function insertUpdate($tableName, array $data, $identifier = null)
    {
        if (is_numeric($identifier)) {
            $identifier = ['id', $identifier];
        }

        $columnNames = array_keys($data);
        $values = array_values($data);

        if ($identifier) {
            $updateSql = implode(', ', array_map(function ($value) {
                return sprintf('%s = ?', $value);
            }, $columnNames));
            $stmt = $this->connection->prepare(sprintf(
                'UPDATE %s SET %s WHERE ? = ?',
                $tableName, $updateSql
            ));
            $values[] = $identifier[0];
            $values[] = $identifier[1];
            $stmt->execute($values);

            return $identifier[1];
        }

        $insertSql = implode(', ', $columnNames);
        $placeholders = implode(', ', array_fill(0, count($columnNames), '?'));

        $stmt = $this->connection->prepare(sprintf(
            'INSERT OR REPLACE INTO %s (%s) VALUES (%s)',
            $tableName,
            $insertSql,
            $placeholders
        ));
        $stmt->execute($values);

        return $this->connection->lastInsertId();
    }
}
