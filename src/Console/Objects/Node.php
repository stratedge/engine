<?php
namespace Stratedge\Engine\Console\Objects;

class Node
{
    protected $class;
    protected $namespace;
    protected $columns = [];

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = ucfirst($class);
        return $this;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumnsAsArrays()
    {
        $arr = [];

        foreach ($this->getColumns() as $column) {
            $arr[] = $column->toArray();
        }

        return $arr;
    }

    public function hasColumns()
    {
        return !empty($this->getColumns());
    }

    public function appendColumn($name, $type)
    {
        $column = new Column($name, $type);
        $this->columns[] = $column;
        return $this;
    }

    public function prependColumn($name, $type)
    {
        $column = new Column($name, $type);
        array_unshift($this->columns, $column);
        return $this;
    }

    public function __toString()
    {
        $str = file_get_contents(__DIR__ . '/../Templates/node.class.tpl');

        $declarations = $definitions = [];

        foreach ($this->getColumns() as $column) {
            $declarations[] = $column->parseDeclaration();
            $definitions[] = $column->parseDefinition();
        }

        return strtr($str, [
            '$namespace' => $this->namespace,
            '$class' => $this->class,
            '$declarations' => implode("\n", $declarations),
            '$definitions' => implode(",\n", $definitions)
        ]);
    }
}
