<?php
namespace Stratedge\Engine\Console\Objects;

class Column
{
    use \Stratedge\Engine\Console\Traits\ParseTemplate;

    protected $name;
    protected $type;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }


    /**
     * Return an array representation of the column's data
     * 
     * @return array
     */
    public function toArray()
    {
        return [$this->name, $this->type];
    }


    /**
     * Returns a string representing the column's declaration statement
     * 
     * @return string
     */
    public function parseDeclaration()
    {
        return $this->parseTemplate(
            __DIR__ . '/../Templates/column.declaration.tpl',
            [
                'name' => $this->name
            ]
        );
    }


    /**
     * Return a string representing the column's definition statement
     * 
     * @return string
     */
    public function parseDefinition()
    {
        switch ($this->type) {
            case 'varchar':
                $type = 'STRING';
                break;
            case 'date_time':
                $type = 'DATETIME';
                break;
            case 'boolean':
                $type = 'BOOL';
            default:
                $type = strtoupper($this->type);
                break;
        }

        return $this->parseTemplate(
            __DIR__ . '/../Templates/column.definition.tpl',
            [
                'name' => $this->name,
                'type' => $type
            ]
        );
    }
}
