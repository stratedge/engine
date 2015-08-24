<?php
namespace Stratedge\Engine\Console\Interfaces;

interface Entity
{
    /**
     * @return string|null
     */
    public function getClassName();


    /**
     * @param  string $class_name
     * @return self
     */
    public function setClassName($class_name);


    /**
     * @return string|null
     */
    public function getNamespace();


    /**
     * @param  string $namespace
     * @return self
     */
    public function setNamespace($namespace);


    /**
     * @return array
     */
    public function getColumns();

    
    /**
     * @return array
     */
    public function getColumnsAsArrays();
    

    /**
     * @return bool
     */
    public function hasColumns();


    /**
     * Adds a column to the beginning of the list of columns
     * 
     * @param  string $column_name
     * @param  string $column_type
     * @return self
     */
    public function prependColumn($column_name, $column_type);


    /**
     * Adds a column to the end of the list of columns
     * 
     * @param  string $column_name
     * @param  string $column_type
     * @return self
     */
    public function appendColumn($column_name, $column_type);


    /**
     * Used to output the final contents of the class file
     * 
     * @return string
     */
    public function __toString();


    /**
     * Used to parse the columns into a string that defines the column's
     * properties
     * 
     * @return string
     */
    public function parseColumnDefinitions();


    /**
     * Used to parse the columns into a string that declares the columns used by
     * the class
     * 
     * @return string
     */
    public function parseColumnDeclarations();
}
