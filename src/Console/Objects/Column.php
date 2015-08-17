<?php
namespace Stratedge\Engine\Console\Objects;

class Column
{
    protected $name;
    protected $type;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function toArray()
    {
        return [$this->name, $this->type];
    }

    public function parseDeclaration()
    {
        $str = file_get_contents(__DIR__ . '/../Templates/node.column.declaration.tpl');

        return strtr($str, [
            '$name' => $this->name
        ]);
    }

    public function parseDefinition()
    {
        $str = file_get_contents(__DIR__ . '/../Templates/node.column.definition.tpl');

        switch ($this->type) {
            case 'integer':
                $type = 'INT';
                break;
            case 'varchar':
            case 'text':
                $type = 'STR';
                break;
        }

        return strtr($str, [
            '$name' => $this->name,
            '$type' => $type
        ]);
    }
}