<?php
namespace Stratedge\Engine\Console\Objects;

use Stratedge\Engine\Console\Interfaces\Entity as EntityInterface;
use Stratedge\Engine\Console\Objects\Column;

abstract class Entity implements EntityInterface
{
    protected $class_name;
    protected $namespace;
    protected $columns = [];


    /**
     * @return string|null
     */
    public function getClassName()
    {
        return $this->class_name;
    }


    /**
     * @param  string $class_name
     * @return self
     */
    public function setClassName($class_name)
    {
        $this->class_name = $class_name;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }


    /**
     * @param  string $namespace
     * @return self
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }


    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }


    /**
     * @return array
     */
    public function getColumnsAsArrays()
    {
        $columns = [];

        foreach ($this->getColumns() as $column) {
            $columns[] = $column->toArray();
        }

        return $columns;
    }


    /**
     * @return bool
     */
    public function hasColumns()
    {
        return empty($this->getColumns()) === false;
    }


    /**
     * Adds a column to the beginning of the list of columns
     * 
     * @param  string $column_name
     * @param  string $column_type
     * @return self
     */
    public function prependColumn($column_name, $column_type)
    {
        array_unshift(
            $this->columns,
            new Column($column_name, $column_type)
        );

        return $this;
    }


    /**
     * Adds a column to the end of the list of columns
     * 
     * @param  string $column_name
     * @param  string $column_type
     * @return self
     */
    public function appendColumn($column_name, $column_type)
    {
        $this->columns[] = new Column($column_name, $column_type);
        return $this;
    }


    /**
     * Used to parse the columns into a string that defines the column's
     * properties
     * 
     * @return string
     */
    public function parseColumnDefinitions()
    {
        $lines = [];

        foreach ($this->getColumns() as $column)
        {
            $lines[] = $column->parseDefinition();
        }

        return implode(",\n", $lines);
    }


    /**
     * Used to parse the columns into a string that declares the columns used by
     * the class
     * 
     * @return string
     */
    public function parseColumnDeclarations()
    {
        $lines = [];

        foreach ($this->getColumns() as $column)
        {
            $lines[] = $column->parseDeclaration();
        }

        return implode("\n", $lines);
    }
}
