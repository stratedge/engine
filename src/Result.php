<?php
namespace Stratedge\Engine;

use BadMethodCallException;
use PDO;
use PDOStatement;
use Stratedge\Engine\Interfaces\Result as ResultInterface;

class Result implements ResultInterface
{
    protected $pdo_statement;


    public function __construct(PDOStatement $pdo_statement)
    {
        $this->setPDOStatement($pdo_statement);
    }


    public function getPDOStatement()
    {
        return $this->pdo_statement;
    }


    public function setPDOStatement($pdo_statement)
    {
        $this->pdo_statement = $pdo_statement;
        return $this;
    }


    public function getObject()
    {
        return $this->getPDOStatement()->fetch(PDO::FETCH_OBJ);
    }


    public function getArray()
    {
        return $this->getPDOStatement()->fetch(PDO::FETCH_ASSOC);
    }


    public function getAllObject()
    {
        return $this->getPDOStatement()->fetchAll(PDO::FETCH_OBJ);
    }


    public function getAllArray()
    {
        return $this->getPDOStatement()->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getColumn($index = 0)
    {
        return $this->getPDOStatement()->fetchColumn($index);
    }


    public function getAllColumn()
    {
        $data = [];

        while ($column = $this->getPDOStatement()->fetchColumn()) {
            $data[] = $column;
        }

        return $data;
    }


    public function __call($name, $arguments)
    {
        if (method_exists($this->getPDOStatement(), $name)) {
            return call_user_func_array(
                [$this->getPDOStatement(), $name],
                $arguments
            );
        }

        throw new BadMethodCallException(
            "Call to undefined method " . get_class() . "::{$name}()"
        );
    }
}
